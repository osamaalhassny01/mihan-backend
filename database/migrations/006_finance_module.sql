-- Finance module enhancement: payment metadata and configurable commission rate
ALTER TABLE payments ADD COLUMN reference VARCHAR(100) DEFAULT NULL AFTER is_simulated;
ALTER TABLE payments ADD COLUMN notes VARCHAR(500) DEFAULT NULL AFTER reference;
INSERT INTO system_settings (`key`,`value`) VALUES ('platform_commission_rate','0.10') ON DUPLICATE KEY UPDATE value=VALUES(value);
