-- Finance workflow: persist the final service price on the request itself.
ALTER TABLE service_requests ADD COLUMN IF NOT EXISTS final_price DECIMAL(10,2) DEFAULT NULL AFTER budget;
