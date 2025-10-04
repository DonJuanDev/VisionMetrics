-- ═══════════════════════════════════════════════════════════
-- VisionMetrics - Database Schema
-- Production-ready Multi-tenant SaaS
-- ═══════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ───────────────────────────────────────────────────────────
-- USERS & AUTHENTICATION
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_identifier` (`identifier`),
  INDEX `idx_ip` (`ip_address`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- MULTI-TENANT (WORKSPACES)
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `workspaces` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `domain` VARCHAR(255) NULL,
  `custom_domain` VARCHAR(255) NULL,
  `custom_domain_verified` BOOLEAN DEFAULT FALSE,
  `custom_domain_ssl` BOOLEAN DEFAULT FALSE,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `plan` ENUM('free', 'starter', 'pro', 'business', 'enterprise') DEFAULT 'free',
  `status` ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
  `trial_ends_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_owner` (`owner_id`),
  INDEX `idx_custom_domain` (`custom_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workspace_members` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` ENUM('owner', 'admin', 'member') DEFAULT 'member',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_membership` (`workspace_id`, `user_id`),
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- BILLING & SUBSCRIPTIONS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `plan` VARCHAR(50) NOT NULL,
  `status` ENUM('pending', 'active', 'cancelled', 'expired', 'suspended') DEFAULT 'pending',
  `mercadopago_preference_id` VARCHAR(255) NULL,
  `mercadopago_payment_id` VARCHAR(255) NULL,
  `mercadopago_subscription_id` VARCHAR(255) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'BRL',
  `current_period_start` TIMESTAMP NULL,
  `current_period_end` TIMESTAMP NULL,
  `cancel_at` TIMESTAMP NULL,
  `cancelled_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `subscription_id` BIGINT UNSIGNED NULL,
  `mercadopago_payment_id` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'BRL',
  `status` VARCHAR(50) NOT NULL,
  `metadata` JSON NULL,
  `paid_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
  INDEX `idx_payment_id` (`mercadopago_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- API KEYS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `key_hash` VARCHAR(255) NOT NULL UNIQUE,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_key_hash` (`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- LEADS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `leads` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NULL,
  `phone_number` VARCHAR(50) NULL,
  `name` VARCHAR(255) NULL,
  `company` VARCHAR(255) NULL,
  `stage` VARCHAR(50) DEFAULT 'novo',
  `score` INT DEFAULT 0,
  `status` ENUM('active', 'converted', 'lost', 'archived') DEFAULT 'active',
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `utm_term` VARCHAR(255) NULL,
  `utm_content` VARCHAR(255) NULL,
  `gclid` VARCHAR(255) NULL,
  `fbclid` VARCHAR(255) NULL,
  `ttclid` VARCHAR(255) NULL,
  `referrer` TEXT NULL,
  `first_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_seen` TIMESTAMP NULL,
  `converted_at` TIMESTAMP NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_phone` (`phone_number`),
  INDEX `idx_stage` (`stage`),
  INDEX `idx_utm_source` (`utm_source`),
  INDEX `idx_utm_campaign` (`utm_campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- EVENTS & TRACKING
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `events` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `lead_id` BIGINT UNSIGNED NULL,
  `event_type` VARCHAR(100) NOT NULL,
  `event_name` VARCHAR(255) NULL,
  `page_url` TEXT NULL,
  `referrer` TEXT NULL,
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `utm_term` VARCHAR(255) NULL,
  `utm_content` VARCHAR(255) NULL,
  `gclid` VARCHAR(255) NULL,
  `fbclid` VARCHAR(255) NULL,
  `ttclid` VARCHAR(255) NULL,
  `user_agent` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `country` VARCHAR(100) NULL,
  `region` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `fingerprint` VARCHAR(64) NULL,
  `idempotency_key` VARCHAR(255) NULL,
  `raw_data` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_idempotency` (`idempotency_key`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attribution_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `event_id` BIGINT UNSIGNED NULL,
  `model` ENUM('first_touch', 'last_touch', 'linear', 'time_decay', 'position_based', 'data_driven') NOT NULL,
  `channel` VARCHAR(100) NULL,
  `campaign` VARCHAR(255) NULL,
  `attribution_value` DECIMAL(5,2) DEFAULT 1.00,
  `touch_points` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_model` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- SESSIONS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- JOBS & WORKER QUEUE
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `jobs_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `event_id` BIGINT UNSIGNED NULL,
  `job_type` VARCHAR(100) NOT NULL,
  `adapter` VARCHAR(50) NULL,
  `payload` JSON NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `tries` INT DEFAULT 0,
  `max_tries` INT DEFAULT 5,
  `response` JSON NULL,
  `error` TEXT NULL,
  `next_run_at` TIMESTAMP NULL,
  `processed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_next_run` (`next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- INTEGRATIONS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `integrations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `provider` VARCHAR(50) NOT NULL,
  `type` VARCHAR(50) NULL,
  `status` ENUM('active', 'inactive', 'error') DEFAULT 'inactive',
  `is_active` BOOLEAN DEFAULT FALSE,
  `credentials` JSON NULL,
  `config` JSON NULL,
  `last_sync_at` TIMESTAMP NULL,
  `last_test_at` TIMESTAMP NULL,
  `test_result` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_integration` (`workspace_id`, `provider`),
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhooks_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NULL,
  `source` VARCHAR(50) NOT NULL,
  `event_type` VARCHAR(100) NULL,
  `payload` JSON NULL,
  `status_code` INT NULL,
  `response` TEXT NULL,
  `received_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_source` (`source`),
  INDEX `idx_received` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- METRICS & ANALYTICS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `metrics_daily` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `metric_type` VARCHAR(50) NOT NULL,
  `channel` VARCHAR(100) NULL,
  `campaign` VARCHAR(255) NULL,
  `value` DECIMAL(15,2) DEFAULT 0.00,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_metric` (`workspace_id`, `date`, `metric_type`, `channel`, `campaign`),
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_date` (`date`),
  INDEX `idx_metric_type` (`metric_type`),
  INDEX `idx_channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- TRACKABLE LINKS & QR CODES
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `trackable_links` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `short_code` VARCHAR(50) NOT NULL UNIQUE,
  `destination_url` TEXT NOT NULL,
  `name` VARCHAR(255) NULL,
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `clicks` INT DEFAULT 0,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr_codes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `link_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` TEXT NOT NULL,
  `qr_data` TEXT NOT NULL,
  `scans` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`link_id`) REFERENCES `trackable_links`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- TAGS & CUSTOM FIELDS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tags` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#3b82f6',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_tag` (`workspace_id`, `name`),
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lead_tags` (
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `tag_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lead_id`, `tag_id`),
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `custom_fields` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `field_type` ENUM('text', 'number', 'date', 'select', 'multiselect') DEFAULT 'text',
  `options` JSON NULL,
  `required` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lead_custom_field_values` (
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `field_id` BIGINT UNSIGNED NOT NULL,
  `value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lead_id`, `field_id`),
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`field_id`) REFERENCES `custom_fields`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- TASKS & WORKFLOWS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `lead_id` BIGINT UNSIGNED NULL,
  `assigned_to` BIGINT UNSIGNED NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
  `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
  `due_date` TIMESTAMP NULL,
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workflows` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `trigger_type` VARCHAR(100) NOT NULL,
  `trigger_config` JSON NULL,
  `actions` JSON NULL,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────────────────────────────────────────────────────────
-- WHATSAPP & CONVERSATIONS
-- ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `whatsapp_numbers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(255) NULL,
  `profile_picture` TEXT NULL,
  `status` ENUM('pending', 'connected', 'disconnected', 'error') DEFAULT 'pending',
  `qr_code` TEXT NULL,
  `session_data` JSON NULL,
  `webhook_url` VARCHAR(500) NULL,
  `last_connected_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_phone` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `whatsapp_number_id` BIGINT UNSIGNED NULL,
  `lead_id` BIGINT UNSIGNED NULL,
  `contact_phone` VARCHAR(50) NOT NULL,
  `contact_name` VARCHAR(255) NULL,
  `contact_picture` TEXT NULL,
  `status` ENUM('active', 'closed', 'archived') DEFAULT 'active',
  `is_sale` BOOLEAN DEFAULT FALSE,
  `journey_stage` VARCHAR(50) DEFAULT 'awareness',
  `utm_source` VARCHAR(255) NULL,
  `utm_medium` VARCHAR(255) NULL,
  `utm_campaign` VARCHAR(255) NULL,
  `utm_term` VARCHAR(255) NULL,
  `utm_content` VARCHAR(255) NULL,
  `gclid` VARCHAR(255) NULL,
  `fbclid` VARCHAR(255) NULL,
  `ttclid` VARCHAR(255) NULL,
  `last_message_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`workspace_id`) REFERENCES `workspaces`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`whatsapp_number_id`) REFERENCES `whatsapp_numbers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_whatsapp` (`whatsapp_number_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_contact_phone` (`contact_phone`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `whatsapp_message_id` VARCHAR(255) NULL,
  `direction` ENUM('inbound', 'outbound') NOT NULL,
  `type` VARCHAR(50) DEFAULT 'text',
  `content` TEXT NULL,
  `media_url` TEXT NULL,
  `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `metadata` JSON NULL,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;