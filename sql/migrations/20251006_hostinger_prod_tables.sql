-- ═══════════════════════════════════════════════════════════
-- VisionMetrics - Hostinger Production Migration
-- Date: 2025-10-06
-- Purpose: Add queue_jobs, link_clicks, conversions, and enhance existing tables
-- ═══════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ───────────────────────────────────────────────────────────
-- QUEUE JOBS TABLE (for CRON worker processing)
-- ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `queue_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'click, conversion, whatsapp_message, etc',
  `payload` JSON NOT NULL COMMENT 'Event data to process',
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `attempts` INT DEFAULT 0,
  `max_attempts` INT DEFAULT 5,
  `error_message` TEXT NULL,
  `response` JSON NULL COMMENT 'Adapter response data',
  `next_run_at` TIMESTAMP NULL,
  `processed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`type`),
  INDEX `idx_next_run` (`next_run_at`),
  INDEX `idx_processing` (`status`, `next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Job queue for server-side event processing (GA4, Meta, etc)';

-- ───────────────────────────────────────────────────────────
-- LINK CLICKS TABLE (for shortener tracking)
-- ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `link_clicks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `trackable_link_id` BIGINT UNSIGNED NOT NULL,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `cookie_token` VARCHAR(255) NULL COMMENT 'vm_first_touch cookie UUID',
  `lead_id` BIGINT UNSIGNED NULL COMMENT 'Associated lead if identified',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `referrer` TEXT NULL,
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `utm_term` VARCHAR(255) NULL,
  `utm_content` VARCHAR(255) NULL,
  `gclid` VARCHAR(255) NULL,
  `fbclid` VARCHAR(255) NULL,
  `ttclid` VARCHAR(255) NULL,
  `country` VARCHAR(100) NULL,
  `region` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trackable_link_id`) REFERENCES `trackable_links`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  INDEX `idx_link` (`trackable_link_id`),
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_cookie_token` (`cookie_token`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_created` (`created_at`),
  INDEX `idx_utm_campaign` (`utm_campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Click tracking with first-touch attribution via cookie';

-- ───────────────────────────────────────────────────────────
-- CONVERSIONS TABLE (for purchase/subscription tracking)
-- ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `conversions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `lead_id` BIGINT UNSIGNED NULL,
  `subscription_id` BIGINT UNSIGNED NULL,
  `conversation_id` BIGINT UNSIGNED NULL COMMENT 'WhatsApp conversation if applicable',
  `conversion_type` VARCHAR(50) NOT NULL COMMENT 'purchase, subscription, trial, etc',
  `provider` VARCHAR(50) NULL COMMENT 'mercadopago, stripe, manual, etc',
  `provider_payment_id` VARCHAR(255) NULL COMMENT 'External payment ID for idempotency',
  `value` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'BRL',
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `utm_term` VARCHAR(255) NULL,
  `utm_content` VARCHAR(255) NULL,
  `idempotency_key` VARCHAR(255) NULL COMMENT 'Unique key to prevent duplicates',
  `metadata` JSON NULL COMMENT 'Additional conversion data',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_provider_payment` (`provider`, `provider_payment_id`),
  UNIQUE KEY `unique_idempotency` (`idempotency_key`),
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_subscription` (`subscription_id`),
  INDEX `idx_type` (`conversion_type`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Conversion events with attribution and idempotency';

-- ───────────────────────────────────────────────────────────
-- ENHANCE LEADS TABLE
-- ───────────────────────────────────────────────────────────
ALTER TABLE `leads`
ADD COLUMN IF NOT EXISTS `first_touch_token` VARCHAR(255) NULL COMMENT 'vm_first_touch cookie UUID' AFTER `phone_number`,
ADD INDEX IF NOT EXISTS `idx_first_touch_token` (`first_touch_token`);

-- ───────────────────────────────────────────────────────────
-- ENHANCE TRACKABLE_LINKS TABLE
-- ───────────────────────────────────────────────────────────
-- Check if slug column exists, if not add it
ALTER TABLE `trackable_links`
ADD COLUMN IF NOT EXISTS `slug` VARCHAR(100) NOT NULL UNIQUE AFTER `short_code` COMMENT 'URL-friendly slug for redirect',
ADD COLUMN IF NOT EXISTS `type` ENUM('redirect', 'whatsapp') DEFAULT 'redirect' AFTER `destination_url` COMMENT 'Link type',
ADD COLUMN IF NOT EXISTS `whatsapp_phone` VARCHAR(50) NULL AFTER `type` COMMENT 'WhatsApp number for deeplink',
ADD COLUMN IF NOT EXISTS `whatsapp_message_template` TEXT NULL AFTER `whatsapp_phone` COMMENT 'Pre-filled message template',
ADD COLUMN IF NOT EXISTS `expires_at` TIMESTAMP NULL AFTER `active` COMMENT 'Link expiration date',
ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE AFTER `active` COMMENT 'Active status',
ADD COLUMN IF NOT EXISTS `total_clicks` INT DEFAULT 0 AFTER `clicks` COMMENT 'Total click count';

-- Update existing records to populate slug from short_code if needed
UPDATE `trackable_links` SET `slug` = `short_code` WHERE `slug` IS NULL OR `slug` = '';

-- ───────────────────────────────────────────────────────────
-- ENHANCE CONVERSATIONS TABLE (for WhatsApp attribution)
-- ───────────────────────────────────────────────────────────
ALTER TABLE `conversations`
ADD COLUMN IF NOT EXISTS `first_touch_token` VARCHAR(255) NULL COMMENT 'vm_first_touch cookie from lead' AFTER `lead_id`,
ADD INDEX IF NOT EXISTS `idx_first_touch_token` (`first_touch_token`);

-- ───────────────────────────────────────────────────────────
-- WHATSAPP CONVERSATIONS TABLE (if doesn't exist from older schema)
-- ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `whatsapp_conversations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `whatsapp_number_id` BIGINT UNSIGNED NULL,
  `lead_id` BIGINT UNSIGNED NULL,
  `contact_phone` VARCHAR(50) NOT NULL,
  `contact_name` VARCHAR(255) NULL,
  `first_touch_token` VARCHAR(255) NULL COMMENT 'vm_first_touch cookie if captured',
  `last_message_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`whatsapp_number_id`) REFERENCES `whatsapp_numbers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_whatsapp_number` (`whatsapp_number_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_contact_phone` (`contact_phone`),
  INDEX `idx_first_touch_token` (`first_touch_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='WhatsApp conversations with lead attribution via cookie token';

-- ───────────────────────────────────────────────────────────
-- CREATE RATE LIMIT TRACKING TABLE (for shortener)
-- ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rate_limit_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(255) NOT NULL COMMENT 'IP address or user identifier',
  `action` VARCHAR(50) NOT NULL COMMENT 'redirect, api, etc',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_identifier_action` (`identifier`, `action`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Rate limiting tracking for various actions';

-- ───────────────────────────────────────────────────────────
-- CLEANUP AND OPTIMIZATION
-- ───────────────────────────────────────────────────────────

-- Remove duplicate slug/short_code if both exist and are the same
-- (This is a safety measure for existing data)

SET FOREIGN_KEY_CHECKS = 1;

-- ═══════════════════════════════════════════════════════════
-- END OF MIGRATION
-- ═══════════════════════════════════════════════════════════




