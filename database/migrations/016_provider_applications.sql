-- Migration 016: Provider Applications and Auto-verification
CREATE TABLE IF NOT EXISTS provider_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    national_id VARCHAR(50) NULL,
    profession VARCHAR(100) NOT NULL,
    years_exp INT DEFAULT 0,
    city VARCHAR(100) NULL,
    bio TEXT NULL,
    certificate_url VARCHAR(500) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ai_score INT DEFAULT 0,
    ai_recommendation VARCHAR(50) NULL,
    ai_notes TEXT NULL,
    admin_notes TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    CONSTRAINT fk_pa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
