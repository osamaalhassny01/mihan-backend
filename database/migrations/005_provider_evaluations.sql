-- ---------------------------------------------------------
-- Provider platform evaluations
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS provider_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    admin_id INT NOT NULL,
    quality_score TINYINT NOT NULL,
    reliability_score TINYINT NOT NULL,
    communication_score TINYINT NOT NULL,
    compliance_score TINYINT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider_eval(provider_id, created_at),
    FOREIGN KEY(provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY(admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

