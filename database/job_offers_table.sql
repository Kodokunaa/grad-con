-- Job Offers Table
CREATE TABLE IF NOT EXISTS job_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    alumni_id INT NOT NULL,
    offer_token VARCHAR(255) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    status ENUM('sent', 'accepted', 'declined', 'expired') DEFAULT 'sent',
    accepted_at TIMESTAMP NULL,
    declined_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alumni_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (offer_token),
    INDEX idx_alumni_status (alumni_id, status),
    INDEX idx_employer_status (employer_id, status)
);
