<?php
require_once __DIR__ . '/../core/Model.php';
class AIProviderSetting extends Model
{
    protected string $table='ai_provider_settings';
    private function ensureTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS ai_provider_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider VARCHAR(50) NOT NULL UNIQUE,
            model VARCHAR(150) DEFAULT NULL,
            api_key_encrypted TEXT DEFAULT NULL,
            base_url VARCHAR(500) DEFAULT NULL,
            temperature DECIMAL(4,2) DEFAULT 0.20,
            max_tokens INT DEFAULT 700,
            timeout INT DEFAULT 20,
            system_prompt TEXT DEFAULT NULL,
            is_enabled TINYINT(1) NOT NULL DEFAULT 0,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function ensureLocalFallback(): void
    {
        $this->ensureTable();
        $row=$this->first(['provider'=>'local']);
        if(!$row){
            $this->insert([
                'provider'=>'local',
                'model'=>null,
                'base_url'=>null,
                'temperature'=>0.20,
                'max_tokens'=>700,
                'timeout'=>20,
                'system_prompt'=>'',
                'is_enabled'=>1,
                'is_default'=>0,
            ]);
        } else if((int)($row['is_enabled']??0)!==1){
            $this->update($row['id'],['is_enabled'=>1]);
        }
    }

    public function allProviders(): array { $this->ensureLocalFallback(); return $this->all('provider'); }
    public function getProvider(string $provider): ?array { $this->ensureLocalFallback(); return $this->first(['provider'=>$provider]); }

    /**
     * Returns the configured default external/local provider.
     * The local Rule-Based provider is always guaranteed to exist as a fallback.
     */
    public function active(): ?array
    {
        $this->ensureLocalFallback();

        // Prefer the explicitly marked default provider.
        $active=$this->first(['is_enabled'=>1,'is_default'=>1]);
        if ($active) return $active;

        // Recover gracefully if an older installation has an enabled external
        // provider but its default flag was not persisted correctly.
        $enabled=$this->where(['is_enabled'=>1], 'id');
        foreach ($enabled as $row) {
            if (($row['provider'] ?? '') !== 'local' && trim((string)($row['api_key_encrypted'] ?? '')) !== '') {
                return $row;
            }
        }

        return $this->first(['provider'=>'local']);
    }

    public function saveProvider(string $provider,array $data): void
    {
        $this->ensureLocalFallback();
        $existing=$this->getProvider($provider);
        if($existing){$this->update($existing['id'],$data);}
        else {$this->insert(['provider'=>$provider]+$data);}
    }

    public function makeDefault(string $provider): void
    {
        $this->ensureLocalFallback();
        $this->execute('UPDATE ai_provider_settings SET is_default=0');
        $row=$this->getProvider($provider);
        if(!$row){
            if($provider==='local'){
                $this->ensureLocalFallback();
                $row=$this->getProvider('local');
            } else {
                throw new RuntimeException('مزود الذكاء الاصطناعي غير مهيأ.');
            }
        }
        $this->execute('UPDATE ai_provider_settings SET is_default=1,is_enabled=1 WHERE id=:id',['id'=>(int)$row['id']]);
        // Rule-Based is the permanent fallback and must remain available.
        if($provider!=='local'){
            $local=$this->getProvider('local');
            if($local) $this->update($local['id'],['is_enabled'=>1,'is_default'=>0]);
        }
    }
}
