-- FCM push notification device tokens
ALTER TABLE users ADD COLUMN IF NOT EXISTS fcm_token VARCHAR(255) DEFAULT NULL AFTER profile_photo;
