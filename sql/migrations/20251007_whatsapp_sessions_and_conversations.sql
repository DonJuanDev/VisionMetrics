-- Migration: WhatsApp Integrations, Sessions & Conversations (Multi-tenant SaaS)
-- Date: 2025-10-07
-- Purpose: Enable workspace-level WhatsApp QR integration with BSP providers

-- =============================================================================
-- whatsapp_integrations: Store encrypted BSP credentials per workspace
-- =============================================================================
CREATE TABLE IF NOT EXISTS whatsapp_integrations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'e.g. 360dialog, infobip, cloud_api',
    name VARCHAR(255) DEFAULT NULL COMMENT 'User-friendly name',
    credentials TEXT NOT NULL COMMENT 'AES-256-GCM encrypted JSON credentials',
    status ENUM('inactive', 'active', 'error') NOT NULL DEFAULT 'inactive',
    meta JSON DEFAULT NULL COMMENT 'Additional metadata (phone_id, business_number, etc)',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_workspace_provider (workspace_id, provider),
    KEY idx_workspace_id (workspace_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encrypted WhatsApp integration credentials per workspace';

-- =============================================================================
-- whatsapp_sessions: QR code sessions and connection status
-- =============================================================================
CREATE TABLE IF NOT EXISTS whatsapp_sessions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    integration_id BIGINT NOT NULL,
    session_id VARCHAR(191) NULL COMMENT 'BSP-provided session identifier',
    qr_image_url TEXT NULL COMMENT 'URL or base64-encoded QR image',
    status ENUM('pending', 'connected', 'disconnected', 'error') DEFAULT 'pending',
    last_heartbeat DATETIME NULL COMMENT 'Last successful connection check',
    error_message TEXT NULL COMMENT 'Error details if status=error',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (integration_id) REFERENCES whatsapp_integrations(id) ON DELETE CASCADE,
    KEY idx_integration_id (integration_id),
    KEY idx_session_id (session_id),
    KEY idx_status (status),
    KEY idx_last_heartbeat (last_heartbeat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WhatsApp QR session state tracking';

-- =============================================================================
-- whatsapp_conversations: Conversation threads (workspace-scoped)
-- =============================================================================
CREATE TABLE IF NOT EXISTS whatsapp_conversations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT NOT NULL,
    lead_id BIGINT NULL COMMENT 'Matched lead (if identified)',
    wa_from VARCHAR(32) NOT NULL COMMENT 'E.164 phone number of customer',
    wa_to VARCHAR(32) NULL COMMENT 'Business WhatsApp number',
    session_id VARCHAR(191) NULL COMMENT 'Associated session identifier',
    snippet TEXT NULL COMMENT 'Preview of last message',
    last_message_at DATETIME NULL COMMENT 'Timestamp of most recent message',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_workspace_id (workspace_id),
    KEY idx_lead_id (lead_id),
    KEY idx_wa_from (wa_from),
    KEY idx_last_message_at (last_message_at),
    UNIQUE KEY ux_workspace_wa_from_wa_to (workspace_id, wa_from, wa_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WhatsApp conversation threads';

-- =============================================================================
-- whatsapp_messages: Individual messages (inbound/outbound)
-- =============================================================================
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    workspace_id BIGINT NOT NULL,
    message_id VARCHAR(191) NULL COMMENT 'BSP message identifier',
    direction ENUM('inbound', 'outbound') NOT NULL,
    text TEXT NULL COMMENT 'Message text content',
    media_url TEXT NULL COMMENT 'URL to media attachment',
    media_type VARCHAR(50) NULL COMMENT 'image, video, document, etc',
    raw_payload JSON NULL COMMENT 'Full webhook payload for debugging',
    received_at DATETIME NULL COMMENT 'Message timestamp from BSP',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE,
    KEY idx_conversation_id (conversation_id),
    KEY idx_workspace_id (workspace_id),
    KEY idx_message_id (message_id),
    KEY idx_direction (direction),
    KEY idx_received_at (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WhatsApp messages (inbound and outbound)';

-- =============================================================================
-- webhooks_logs: Audit log for incoming webhooks
-- =============================================================================
CREATE TABLE IF NOT EXISTS webhooks_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(50) NOT NULL COMMENT 'e.g. whatsapp, meta, google',
    workspace_id BIGINT NULL COMMENT 'Identified workspace (if applicable)',
    payload JSON NOT NULL COMMENT 'Raw webhook payload',
    headers JSON NULL COMMENT 'Request headers for debugging',
    ip_address VARCHAR(45) NULL COMMENT 'Source IP',
    processing_status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    received_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    KEY idx_source (source),
    KEY idx_workspace_id (workspace_id),
    KEY idx_processing_status (processing_status),
    KEY idx_received_at (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Webhook audit log for all integrations';

-- =============================================================================
-- Add indexes for efficient lookups
-- =============================================================================

-- Index for vm_token attribution lookups
ALTER TABLE leads ADD INDEX IF NOT EXISTS idx_first_touch_token (first_touch_token);

-- Index for phone number matching
ALTER TABLE leads ADD INDEX IF NOT EXISTS idx_phone (phone);

-- =============================================================================
-- Migration complete
-- =============================================================================



