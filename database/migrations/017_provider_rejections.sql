-- Migration 017: Record individual provider rejections so rejected requests disappear from the rejecting provider but remain available for others
CREATE TABLE IF NOT EXISTS provider_rejections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    provider_id INT NOT NULL,
    reason VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_req_prov (request_id, provider_id),
    INDEX idx_prov (provider_id),
    CONSTRAINT fk_pr_req FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_pr_prov FOREIGN KEY (provider_id) REFERENCES provider_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
