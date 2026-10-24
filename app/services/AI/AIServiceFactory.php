<?php
require_once __DIR__ . '/AIProviderInterface.php';
require_once __DIR__ . '/providers/RuleBasedAIProvider.php';
require_once __DIR__ . '/providers/OpenAIProvider.php';
require_once __DIR__ . '/providers/GroqProvider.php';
require_once __DIR__ . '/providers/GeminiProvider.php';
require_once __DIR__ . '/providers/OpenAICompatibleProvider.php';
require_once __DIR__ . '/Encryption.php';
require_once __DIR__ . '/../../models/AIProviderSetting.php';
require_once __DIR__ . '/../../core/Model.php';

/**
 * Central AI factory.
 *
 * Rule-Based AI is a permanent, free fallback. Any external provider failure
 * (including Gemini quota/rate-limit errors such as HTTP 429) automatically
 * switches the current request to Rule-Based AI so the feature keeps working.
 */
class AIServiceFactory
{
    public static function make(?array &$meta=null): AIProviderInterface
    {
        $setting = null;
        try {
            $setting = (new AIProviderSetting())->active();
        } catch (Throwable $e) {
            $setting = null;
        }
        if(!$setting){
            $meta=['provider'=>'local','model'=>null,'fallback'=>false,'fallback_available'=>true,'system_prompt'=>''];
            return new RuleBasedAIProvider();
        }
        try{
            $p=self::provider($setting);
            $meta=[
                'provider'=>$setting['provider'],
                'model'=>$setting['model']??null,
                'fallback'=>false,
                'fallback_available'=>true,
                'system_prompt'=>$setting['system_prompt']??''
            ];
            return $p;
        }catch(Throwable $e){
            $meta=[
                'provider'=>'local',
                'model'=>null,
                'fallback'=>true,
                'fallback_available'=>true,
                'attempted_provider'=>$setting['provider']??'external',
                'attempted_model'=>$setting['model']??null,
                'error'=>'تعذر تهيئة المزود الخارجي، تم التحويل للمحرك المحلي المجاني.'
            ];
            return new RuleBasedAIProvider();
        }
    }

    private static function provider(array $s): AIProviderInterface
    {
        $s['api_key']=AIEncryption::decrypt($s['api_key_encrypted']??'');
        return match($s['provider']){
            'openai'=>new OpenAIProvider($s),
            'groq'=>new GroqProvider($s),
            'gemini'=>new GeminiProvider($s),
            'openai_compatible'=>new OpenAICompatibleProvider($s),
            default=>new RuleBasedAIProvider()
        };
    }

    private static function fallbackAnalyze(string $text, string $sourceProvider, float $started, Throwable $e): array
    {
        $safeError=self::safeError($e->getMessage());
        try {
            $r=(new RuleBasedAIProvider())->analyzeProblem($text);
            self::log(
                'analyze',
                $sourceProvider,
                true,
                true,
                microtime(true)-$started,
                'فشل '.$sourceProvider.' وتم استخدام Rule-Based AI كبديل مجاني: '.$safeError
            );
            $r['engine']='local';
            $r['fallback']=true;
            $r['fallback_reason']=$sourceProvider==='gemini' ? 'حصة Gemini/الاتصال غير متاح؛ تم التحويل تلقائيًا إلى Rule-Based AI.' : 'تعذر استخدام المزود الخارجي؛ تم التحويل تلقائيًا إلى Rule-Based AI.';
            return $r;
        } catch(Throwable $fallbackError) {
            self::log('analyze',$sourceProvider,true,false,microtime(true)-$started,self::safeError($fallbackError->getMessage()));
            throw $fallbackError;
        }
    }

    public static function analyze(string $text): array
    {
        $meta=[];$provider=self::make($meta);$started=microtime(true);
        try{
            $r=$provider->analyzeProblem($text);
            self::log('analyze',$meta['provider'],$meta['fallback']??false,true,microtime(true)-$started,null);
            $r['engine']=$meta['provider'];
            $r['fallback']=$meta['fallback']??false;
            if(!empty($meta['fallback'])) $r['fallback_reason']=$meta['error']??'تم استخدام المحرك المحلي الاحتياطي.';
            return $r;
        }
        catch(Throwable $e){
            // Any external failure, including Gemini HTTP 429 quota/rate-limit errors,
            // is handled by the guaranteed Rule-Based fallback.
            return self::fallbackAnalyze($text,$meta['provider']??'external',$started,$e);
        }
    }

    public static function chat(array $messages,array $options=[]): array
    {
        $meta=[];$provider=self::make($meta);$started=microtime(true);
        try{
            $workingMessages=$messages;
            if(!empty($meta['system_prompt'])) array_unshift($workingMessages,['role'=>'system','content'=>$meta['system_prompt']]);
            $text=$provider->chat($workingMessages,$options);
            self::log('chat',$meta['provider'],$meta['fallback']??false,true,microtime(true)-$started,null);
            return [
                'text'=>$text,
                'provider'=>$meta['provider'],
                'model'=>$meta['model']??null,
                'fallback'=>$meta['fallback']??false,
                'fallback_reason'=>!empty($meta['fallback'])?($meta['error']??'تم استخدام المحرك المحلي الاحتياطي.'):null
            ];
        }
        catch(Throwable $e){
            $sourceProvider=$meta['provider']??'external';
            $safeError=self::safeError($e->getMessage());
            try {
                $text=(new RuleBasedAIProvider())->chat($messages,$options);
                self::log('chat',$sourceProvider,true,true,microtime(true)-$started,
                    'فشل '.$sourceProvider.' وتم استخدام Rule-Based AI كبديل مجاني: '.$safeError
                );
                return [
                    'text'=>$text,
                    'provider'=>'local',
                    'model'=>null,
                    'attempted_provider'=>$sourceProvider,
                    'attempted_model'=>$meta['model']??null,
                    'fallback'=>true,
                    'fallback_reason'=>$sourceProvider==='gemini'
                        ? 'تعذر استخدام Gemini (قد تكون الحصة المجانية قد انتهت أو حدث حد للطلبات)، لذلك تم التحويل تلقائيًا إلى Rule-Based AI المجاني.'
                        : 'تعذر استخدام المزود الخارجي، لذلك تم التحويل تلقائيًا إلى Rule-Based AI المجاني.'
                ];
            } catch(Throwable $fallbackError) {
                self::log('chat',$sourceProvider,true,false,microtime(true)-$started,self::safeError($fallbackError->getMessage()));
                throw $fallbackError;
            }
        }
    }

    private static function safeError(string $message): string
    {
        return (string)mb_substr(
            preg_replace('/(api[_-]?key|authorization|bearer)\s*[:=]?\s*[^\s,;]+/iu','[REDACTED]',$message),
            0,
            500
        );
    }

    private static function log(string $operation,string $provider,bool $fallback,bool $success,float $duration,?string $error): void
    {
        try{
            (new class extends Model{protected string $table='ai_logs';})->insert([
                'user_id'=>Auth::id()?:null,
                'provider'=>$provider,
                'operation'=>$operation,
                'success'=>$success?1:0,
                'fallback_used'=>$fallback?1:0,
                'duration_ms'=>(int)round($duration*1000),
                'error_message'=>$error?self::safeError($error):null
            ]);
        }catch(Throwable $ignore){}
    }
}
