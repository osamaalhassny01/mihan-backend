-- Existing requests retain version 1. New requests explicitly use version 2.
ALTER TABLE service_requests
    MODIFY COLUMN status ENUM('new','pending','accepted','in_progress','awaiting_confirmation','completed','cancelled','rejected') NOT NULL DEFAULT 'new',
    ADD COLUMN workflow_version TINYINT NOT NULL DEFAULT 1,
    ADD COLUMN quote_id INT NULL,
    ADD COLUMN completion_notes TEXT NULL,
    ADD COLUMN rework_notes TEXT NULL;

ALTER TABLE request_status_history
    MODIFY COLUMN old_status VARCHAR(32) NULL,
    MODIFY COLUMN new_status VARCHAR(32) NOT NULL;

CREATE TABLE IF NOT EXISTS request_quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    provider_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    details TEXT NOT NULL,
    visit_at DATETIME NULL,
    status ENUM('pending','approved','declined','superseded') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decided_at DATETIME NULL,
    INDEX idx_quote_request (request_id, id),
    FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
