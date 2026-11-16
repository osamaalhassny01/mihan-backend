-- Migration 019: Timezone Alignment to UTC+3 (Asia/Aden / Asia/Riyadh)
-- Synchronizes previously stored UTC timestamps to match Yemen/Saudi Arabia local time.

UPDATE service_requests 
SET created_at = DATE_ADD(created_at, INTERVAL 3 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 3 HOUR);

UPDATE request_status_history 
SET changed_at = DATE_ADD(changed_at, INTERVAL 3 HOUR);

UPDATE request_quotes 
SET created_at = DATE_ADD(created_at, INTERVAL 3 HOUR),
    decided_at = CASE WHEN decided_at IS NOT NULL THEN DATE_ADD(decided_at, INTERVAL 3 HOUR) ELSE NULL END;

UPDATE provider_applications 
SET created_at = DATE_ADD(created_at, INTERVAL 3 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 3 HOUR);

UPDATE notifications 
SET created_at = DATE_ADD(created_at, INTERVAL 3 HOUR);

UPDATE activity_logs 
SET created_at = DATE_ADD(created_at, INTERVAL 3 HOUR);
