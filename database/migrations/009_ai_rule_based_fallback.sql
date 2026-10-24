-- MIHAN: permanent free Rule-Based AI fallback
-- Safe to run on an existing installation.
INSERT INTO ai_provider_settings
    (provider, model, base_url, api_key_encrypted, temperature, max_tokens, timeout, system_prompt, is_enabled, is_default)
VALUES
    ('local', NULL, NULL, NULL, 0.20, 700, 20,
     'أنت المساعد المحلي المجاني لمنصة مِهَن. أجب بالعربية بوضوح ولا تخترع بيانات.', 1, 0)
ON DUPLICATE KEY UPDATE
    is_enabled = 1;
