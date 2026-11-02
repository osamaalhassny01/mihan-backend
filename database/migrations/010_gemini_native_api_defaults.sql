-- Gemini native REST API defaults for Google AI Studio Auth Keys
UPDATE ai_provider_settings
SET model = CASE
    WHEN model IS NULL OR model = '' OR model LIKE '%3.1%' OR model = 'gemini-2.5-flash' THEN 'gemini-1.5-flash'
    ELSE model
END,
base_url = CASE
    WHEN base_url IS NULL OR base_url = '' OR base_url LIKE '%openai%'
        THEN 'https://generativelanguage.googleapis.com/v1beta'
    ELSE base_url
END
WHERE provider = 'gemini';
