-- Fix trackable links and sales functionality
-- Add missing tables and columns

-- Add missing columns to trackable_links table
ALTER TABLE trackable_links 
ADD COLUMN IF NOT EXISTS slug VARCHAR(50) NOT NULL UNIQUE AFTER short_code,
ADD COLUMN IF NOT EXISTS utm_source VARCHAR(255) NULL AFTER name,
ADD COLUMN IF NOT EXISTS utm_medium VARCHAR(255) NULL AFTER utm_source,
ADD COLUMN IF NOT EXISTS utm_campaign VARCHAR(255) NULL AFTER utm_medium,
ADD COLUMN IF NOT EXISTS utm_term VARCHAR(255) NULL AFTER utm_campaign,
ADD COLUMN IF NOT EXISTS utm_content VARCHAR(255) NULL AFTER utm_term,
ADD COLUMN IF NOT EXISTS active BOOLEAN DEFAULT TRUE AFTER utm_content,
ADD COLUMN IF NOT EXISTS clicks INT DEFAULT 0 AFTER active;

-- Create sales table
CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NULL,
    conversation_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    sale_value DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'refunded') DEFAULT 'pending',
    utm_source VARCHAR(255) NULL,
    utm_medium VARCHAR(255) NULL,
    utm_campaign VARCHAR(255) NULL,
    utm_term VARCHAR(255) NULL,
    utm_content VARCHAR(255) NULL,
    detected_automatically BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
    INDEX idx_workspace (workspace_id),
    INDEX idx_lead (lead_id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to workspaces table for custom domain
ALTER TABLE workspaces
ADD COLUMN IF NOT EXISTS custom_domain VARCHAR(255) NULL AFTER domain,
ADD COLUMN IF NOT EXISTS custom_domain_verified BOOLEAN DEFAULT FALSE AFTER custom_domain,
ADD COLUMN IF NOT EXISTS custom_domain_ssl BOOLEAN DEFAULT FALSE AFTER custom_domain_verified;

-- Add index for custom domain
ALTER TABLE workspaces ADD INDEX IF NOT EXISTS idx_custom_domain (custom_domain);

-- Update trackable_links to use slug instead of short_code in queries
-- (This will be handled in the PHP code)
