USE mihan;
CREATE TABLE IF NOT EXISTS ai_provider_settings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 provider VARCHAR(40) NOT NULL UNIQUE,
 model VARCHAR(120) DEFAULT NULL,
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
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ai_logs (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT DEFAULT NULL,
 provider VARCHAR(40) NOT NULL,
 operation VARCHAR(40) NOT NULL,
 success TINYINT(1) NOT NULL DEFAULT 0,
 fallback_used TINYINT(1) NOT NULL DEFAULT 0,
 duration_ms INT DEFAULT NULL,
 error_message VARCHAR(500) DEFAULT NULL,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_ai_logs_created(created_at), INDEX idx_ai_logs_provider(provider),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
INSERT IGNORE INTO ai_provider_settings(provider,model,base_url,is_enabled,is_default) VALUES
('local',NULL,NULL,1,1),('gemini','gemini-2.5-flash','https://generativelanguage.googleapis.com/v1beta/openai',0,0),('openai','gpt-4o-mini','https://api.openai.com/v1',0,0),('groq',NULL,'https://api.groq.com/openai/v1',0,0),('openai_compatible',NULL,NULL,0,0);
