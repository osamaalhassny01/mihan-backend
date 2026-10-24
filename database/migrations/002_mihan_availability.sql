-- MIHAN migration 002: provider weekly availability
-- Run this once on an existing database.

ALTER TABLE provider_availability
    ADD INDEX idx_provider_availability_provider_day_time (provider_id, day_of_week, start_time, end_time);
