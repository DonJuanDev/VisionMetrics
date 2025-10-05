-- ═══════════════════════════════════════════════════════════════════
-- VISIONMETRICS - INSTALAÇÃO COMPLETA
-- Execute este arquivo ÚNICO no phpMyAdmin
-- ═══════════════════════════════════════════════════════════════════

-- Usar o banco de dados
USE visionmetrics;

-- Limpar se já existir
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `lead_custom_field_values`;
DROP TABLE IF EXISTS `custom_fields`;
DROP TABLE IF EXISTS `lead_tags`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `whatsapp_numbers`;
DROP TABLE IF EXISTS `workflows`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `qr_codes`;
DROP TABLE IF EXISTS `trackable_links`;
DROP TABLE IF EXISTS `metrics_daily`;
DROP TABLE IF EXISTS `webhooks_logs`;
DROP TABLE IF EXISTS `integrations`;
DROP TABLE IF EXISTS `jobs_log`;
DROP TABLE IF EXISTS `attribution_records`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `leads`;
DROP TABLE IF EXISTS `api_keys`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `workspace_members`;
DROP TABLE IF EXISTS `workspaces`;
DROP TABLE IF EXISTS `email_verifications`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

SET NAMES utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- TABELAS
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE `users` (
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

CREATE TABLE `password_resets` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_identifier` (`identifier`),
  INDEX `idx_ip` (`ip_address`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `workspaces` (
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
  INDEX `idx_slug` (`slug`),
  INDEX `idx_owner` (`owner_id`),
  INDEX `idx_custom_domain` (`custom_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `workspace_members` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` ENUM('owner', 'admin', 'member') DEFAULT 'member',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_membership` (`workspace_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subscriptions` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
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
  INDEX `idx_payment_id` (`mercadopago_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_keys` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `key_hash` VARCHAR(255) NOT NULL UNIQUE,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_key_hash` (`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leads` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_phone` (`phone_number`),
  INDEX `idx_stage` (`stage`),
  INDEX `idx_utm_source` (`utm_source`),
  INDEX `idx_utm_campaign` (`utm_campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_idempotency` (`idempotency_key`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attribution_records` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_model` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs_log` (
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
  INDEX `idx_status` (`status`),
  INDEX `idx_next_run` (`next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `integrations` (
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
  UNIQUE KEY `unique_integration` (`workspace_id`, `provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `webhooks_logs` (
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

CREATE TABLE `metrics_daily` (
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
  INDEX `idx_date` (`date`),
  INDEX `idx_metric_type` (`metric_type`),
  INDEX `idx_channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trackable_links` (
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
  INDEX `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `qr_codes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `link_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` TEXT NOT NULL,
  `qr_data` TEXT NOT NULL,
  `scans` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tags` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#3b82f6',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_tag` (`workspace_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lead_tags` (
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `tag_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lead_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `custom_fields` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `field_type` ENUM('text', 'number', 'date', 'select', 'multiselect') DEFAULT 'text',
  `options` JSON NULL,
  `required` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lead_custom_field_values` (
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `field_id` BIGINT UNSIGNED NOT NULL,
  `value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lead_id`, `field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tasks` (
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
  INDEX `idx_status` (`status`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `workflows` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `trigger_type` VARCHAR(100) NOT NULL,
  `trigger_config` JSON NULL,
  `actions` JSON NULL,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `whatsapp_numbers` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_phone` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `conversations` (
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
  INDEX `idx_workspace` (`workspace_id`),
  INDEX `idx_whatsapp` (`whatsapp_number_id`),
  INDEX `idx_lead` (`lead_id`),
  INDEX `idx_contact_phone` (`contact_phone`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
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
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════════
-- BANCO PRONTO! ✅
-- ═══════════════════════════════════════════════════════════════════


