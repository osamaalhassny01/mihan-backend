-- Fix and standardize Google Gemini model to gemini-1.5-flash with native endpoint
UPDATE ai_provider_settings
SET model = 'gemini-2.5-flash',
    base_url = 'https://generativelanguage.googleapis.com/v1beta',
    max_tokens = 2048,
    temperature = 0.40,
    timeout = 30
WHERE provider = 'gemini';

