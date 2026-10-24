-- Gemini native REST API defaults for Google AI Studio Auth Keys
UPDATE ai_provider_settings
SET model = CASE
    WHEN model IS NULL OR model = '' OR model = 'gemini-2.5-flash' THEN 'gemini-3.1-flash-lite'
    ELSE model
END,
base_url = CASE
    WHEN base_url IS NULL OR base_url = '' OR base_url = 'https://generativelanguage.googleapis.com/v1beta/openai'
        THEN 'https://generativelanguage.googleapis.com/v1beta'
    ELSE base_url
END
WHERE provider = 'gemini';
