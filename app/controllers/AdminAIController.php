<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/AIProviderSetting.php';
require_once __DIR__ . '/../services/AI/AIServiceFactory.php';
require_once __DIR__ . '/../services/AI/Encryption.php';
require_once __DIR__ . '/../services/AI/providers/RuleBasedAIProvider.php';
require_once __DIR__ . '/../services/AI/providers/OpenAIProvider.php';
require_once __DIR__ . '/../services/AI/providers/GroqProvider.php';
require_once __DIR__ . '/../services/AI/providers/GeminiProvider.php';
require_once __DIR__ . '/../services/AI/providers/OpenAICompatibleProvider.php';
require_once __DIR__ . '/../services/AnalyticsService.php';
require_once __DIR__ . '/../models/ProviderEvaluation.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
class AdminAIController extends Controller
{
    public function __construct()
    {
        Auth::requireRole('admin');
        Auth::requirePermission('manage_ai');
    }
    private function providers(): array { return ['local'=>'المحلي (Rule-Based)','gemini'=>'Google Gemini','openai'=>'OpenAI','groq'=>'Groq','openai_compatible'=>'OpenAI Compatible / Custom']; }
    public function index()
    {
        $model=new AIProviderSetting();
        $rows=$model->allProviders();$by=[];foreach($rows as $r)$by[$r['provider']]=$r;
        $db=Database::getInstance();
        $stats=['total'=>0,'success'=>0,'failed'=>0,'fallback'=>0,'avg_ms'=>0,'today'=>0,'by_provider'=>[],'recent'=>[]];
        try {
            $stats['total']=(int)$db->query("SELECT COUNT(*) FROM ai_logs")->fetchColumn();
            $stats['success']=(int)$db->query("SELECT COUNT(*) FROM ai_logs WHERE success=1")->fetchColumn();
            $stats['failed']=(int)$db->query("SELECT COUNT(*) FROM ai_logs WHERE success=0")->fetchColumn();
            $stats['fallback']=(int)$db->query("SELECT COUNT(*) FROM ai_logs WHERE fallback_used=1")->fetchColumn();
            $stats['avg_ms']=(int)round((float)($db->query("SELECT COALESCE(AVG(duration_ms),0) FROM ai_logs")->fetchColumn() ?: 0));
            $stats['today']=(int)$db->query("SELECT COUNT(*) FROM ai_logs WHERE created_at >= CURDATE()")->fetchColumn();
            $stats['by_provider']=$db->query("SELECT provider, COUNT(*) total, SUM(success=1) success, SUM(success=0) failed, SUM(fallback_used=1) fallback, ROUND(AVG(duration_ms)) avg_ms FROM ai_logs GROUP BY provider ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
            $stats['recent']=$db->query("SELECT l.*,u.name FROM ai_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
        } catch(Throwable $e) {
            // The panel remains usable even if the optional log table is unavailable.
        }
        $this->view('admin/ai/settings',[
            'title'=>'إدارة الذكاء الاصطناعي',
            'providers'=>$this->providers(),
            'settings'=>$by,
            'active'=>$model->active(),
            'stats'=>$stats
        ],'dashboard');
    }
    public function save()
    {
        $this->verifyCsrfOrFail();$provider=$this->input('provider');if(!array_key_exists($provider,$this->providers())){$this->redirect('/admin/ai');return;}
        $m=new AIProviderSetting();$old=$m->getProvider($provider);$key=trim((string)$this->input('api_key',''));$data=['model'=>$this->input('model')?:null,'base_url'=>$this->input('base_url')?:null,'temperature'=>max(0,min(2,(float)$this->input('temperature',0.2))),'max_tokens'=>max(100,min(8000,(int)$this->input('max_tokens',700))),'timeout'=>max(5,min(60,(int)$this->input('timeout',20))),'system_prompt'=>$this->input('system_prompt')?:null,'is_enabled'=>$this->input('is_enabled')==='1'?1:0,'is_default'=>0];
        try {
            if($key!=='')$data['api_key_encrypted']=AIEncryption::encrypt($key);elseif($old)$data['api_key_encrypted']=$old['api_key_encrypted'];
            $m->saveProvider($provider,$data);
            if($data['is_enabled'])$m->makeDefault($provider);
            elseif($old&&$old['is_default']){$m->makeDefault('local');}
            Session::flash('success','تم حفظ إعدادات مزود '.$this->providers()[$provider]);
        } catch(Throwable $e) {
            Session::flash('error','تعذر حفظ إعدادات الذكاء الاصطناعي: '.$this->safeError($e->getMessage()));
        }
        $this->redirect('/admin/ai');
    }
    public function test()
    {
        $this->verifyCsrfOrFail();
        $provider=$this->input('provider');
        if (!array_key_exists($provider,$this->providers())) {
            $this->json(['ok'=>false,'message'=>'المزود غير موجود'],404);
            return;
        }

        $m=new AIProviderSetting();
        $saved=$m->getProvider($provider) ?: ['provider'=>$provider];
        $started=microtime(true);
        $postedKey=trim((string)$this->input('api_key',''));
        $savedKey=AIEncryption::decrypt($saved['api_key_encrypted']??'');
        $row=$saved;
        $row['provider']=$provider;
        // The test must use the values currently visible in the form. This allows
        // testing a new key/model/Base URL before saving them.
        $row['api_key']=$postedKey!=='' ? $postedKey : $savedKey;
        foreach(['model','base_url','system_prompt'] as $field){
            $value=$this->input($field,null);
            if($value!==null) $row[$field]=trim((string)$value);
        }
        foreach(['temperature','max_tokens','timeout'] as $field){
            $value=$this->input($field,null);
            if($value!==null && $value!=='') $row[$field]=$value;
        }

        try {
            $p=$this->buildProvider($provider,$row);
            $test=$p->testConnection();
            $this->logTest($provider,!empty($test['ok']),microtime(true)-$started,!empty($test['ok'])?null:(string)($test['message']??'فشل الاختبار.'));
            $this->json($test);
        } catch(Throwable $e) {
            $safe=$this->safeError($e->getMessage());
            $this->logTest($provider,false,microtime(true)-$started,$safe);
            $this->json(['ok'=>false,'message'=>'فشل الاختبار: '.$safe],422);
        }
    }
    private function buildProvider(string $provider, array $row): AIProviderInterface
    {
        if ($provider==='local') return new RuleBasedAIProvider();
        $row['provider']=$provider;
        // Keep an explicitly supplied API key (for example from the unsaved test form).
        // Only decrypt the saved key when no plain key was supplied.
        if (trim((string)($row['api_key'] ?? '')) === '') {
            $row['api_key']=AIEncryption::decrypt($row['api_key_encrypted']??'');
        }
        return match($provider){
            'openai'=>new OpenAIProvider($row),
            'groq'=>new GroqProvider($row),
            'gemini'=>new GeminiProvider($row),
            'openai_compatible'=>new OpenAICompatibleProvider($row),
            default=>throw new RuntimeException('مزود غير معروف')
        };
    }

    public function testAll()
    {
        $this->verifyCsrfOrFail();
        $model=new AIProviderSetting();$rows=$model->allProviders();$results=[];
        foreach($this->providers() as $provider=>$label){
            $row=null;foreach($rows as $r){if($r['provider']===$provider){$row=$r;break;}}
            $started=microtime(true);
            try {
                if($provider!=='local' && empty(AIEncryption::decrypt($row['api_key_encrypted']??''))){
                    $results[]=['provider'=>$provider,'label'=>$label,'ok'=>false,'status'=>'not_configured','message'=>'لم يتم إدخال API Key لهذا النموذج.'];
                    continue;
                }
                $p=$this->buildProvider($provider,$row??['provider'=>$provider]);
                $r=$p->testConnection();
                $ok=!empty($r['ok']);
                $message=(string)($r['message']??($ok?'اتصال ناجح.':'فشل الاختبار.'));
                $results[]=['provider'=>$provider,'label'=>$label,'ok'=>$ok,'status'=>$ok?'success':'failed','message'=>$message];
                $this->logTest($provider,$ok,microtime(true)-$started,$ok?null:$message);
            } catch(Throwable $e) {
                $message=$this->safeError($e->getMessage());
                $results[]=['provider'=>$provider,'label'=>$label,'ok'=>false,'status'=>'failed','message'=>$message];
                $this->logTest($provider,false,microtime(true)-$started,$message);
            }
        }
        $this->json(['ok'=>true,'results'=>$results]);
    }

    private function logTest(string $provider,bool $success,float $duration,?string $error): void
    {
        try {(new class extends Model{protected string $table='ai_logs';})->insert([
            'user_id'=>Auth::id()?:null,'provider'=>$provider,'operation'=>'connection_test',
            'success'=>$success?1:0,'fallback_used'=>0,'duration_ms'=>(int)round($duration*1000),
            'error_message'=>$error?mb_substr($this->safeError($error),0,500):null
        ]);} catch(Throwable $ignore) {}
    }

    private function safeError(string $message): string
    {
        return (string)mb_substr(preg_replace('/(api[_-]?key|authorization|bearer)\s*[:=]?\s*[^\s,;]+/iu','[REDACTED]',$message),0,500);
    }

    public function logs()
    {
        // Legacy route kept for backward compatibility; the AI dashboard is now the single management page.
        $this->redirect('/admin/ai');
    }

    public function providerAnalysis($id)
    {
        $db=Database::getInstance(); $stmt=$db->prepare("SELECT pp.*,u.name,u.email,u.phone FROM provider_profiles pp JOIN users u ON u.id=pp.user_id WHERE pp.id=:id LIMIT 1"); $stmt->execute(['id'=>(int)$id]); $provider=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$provider){Session::flash('error','مقدم الخدمة غير موجود');$this->redirect('/admin/providers');return;}
        $stats=$db->prepare("SELECT COUNT(*) review_count, COALESCE(AVG(rating),0) customer_avg FROM reviews WHERE provider_id=:p");$stats->execute(['p'=>(int)$id]);$stats=$stats->fetch(PDO::FETCH_ASSOC);
        $latest=(new ProviderEvaluation())->latest((int)$id); $ev=(new ProviderEvaluation())->objectiveScore($provider); $combined=(new ProviderEvaluation())->combinedScore($provider,$latest);
        $data=['provider'=>$provider,'customer_reviews'=>$stats,'latest_platform_evaluation'=>$latest,'objective_platform_score'=>$ev,'combined_score'=>$combined];
        $system='أنت محلل أداء لمقدم خدمات في منصة مِهَن. حلل البيانات المعطاة فقط. قارن بين تقييم العملاء والتقييم الإداري ومؤشرات الأداء. اذكر نقاط القوة والمخاطر وإجراءات تحسين عملية، ولا تخترع أي أرقام.';
        $res=AIServiceFactory::chat([['role'=>'system','content'=>$system],['role'=>'user','content'=>'حلل مقدم الخدمة التالي:\n'.json_encode($data,JSON_UNESCAPED_UNICODE)]],['temperature'=>0.2,'max_tokens'=>800]);
        $this->view('admin/ai/provider_analysis',['title'=>'تحليل أداء مقدم الخدمة','provider'=>$provider,'data'=>$data,'answer'=>$res['text'],'meta'=>$res],'dashboard');
    }

    public function decisionSupport()
    {
        $question=$this->input('question','');$answer=null;$meta=null;
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->verifyCsrfOrFail();$analytics=new AnalyticsService();$data=$analytics->decisionSummary([])+['insights'=>$analytics->decisionInsights([]),'top_services'=>$analytics->mostRequestedServices([],5),'top_areas'=>$analytics->mostActiveAreas(5,[])];$system='أنت مساعد دعم قرار لإدارة منصة مهنية. حلل بيانات JSON المعطاة فقط، ولا تخترع أرقامًا. اذكر ملاحظات عملية ومخاطر واقتراحات قابلة للتنفيذ.';$res=AIServiceFactory::chat([['role'=>'system','content'=>$system],['role'=>'user','content'=>($question?:'حلل أداء المنصة واقترح أولويات الإدارة.')."\nDATA:\n".json_encode($data,JSON_UNESCAPED_UNICODE)]],['temperature'=>0.2,'max_tokens'=>900]);$answer=$res['text'];$meta=$res;}
        $this->view('admin/ai/decision_support',['title'=>'دعم القرار بالذكاء الاصطناعي','answer'=>$answer,'meta'=>$meta],'dashboard');
    }
}
