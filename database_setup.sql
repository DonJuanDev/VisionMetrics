-- VisionMetrics Database Setup
-- Execute este script no seu MySQL para criar o banco e as tabelas

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS visionmetrics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE visionmetrics;

-- Tabela de empresas
CREATE TABLE IF NOT EXISTS companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    cnpj VARCHAR(18),
    timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo',
    trial_expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_companies_active (is_active),
    INDEX idx_companies_trial (trial_expires_at)
);

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'company_admin', 'company_agent', 'company_viewer') NOT NULL,
    phone VARCHAR(50),
    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,
    two_factor_confirmed_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_users_company (company_id),
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
);

-- Tabela de leads
CREATE TABLE IF NOT EXISTS leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    origin ENUM('meta', 'google', 'outras', 'nao_rastreada') NOT NULL,
    source_url TEXT,
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    utm_content VARCHAR(255),
    utm_term VARCHAR(255),
    status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    tags JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_leads_company (company_id),
    INDEX idx_leads_phone (phone),
    INDEX idx_leads_origin (origin),
    INDEX idx_leads_status (status),
    INDEX idx_leads_created (created_at)
);

-- Tabela de conversas
CREATE TABLE IF NOT EXISTS conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    whatsapp_chat_id VARCHAR(255),
    platform ENUM('whatsapp', 'telegram', 'instagram', 'facebook') DEFAULT 'whatsapp',
    status ENUM('active', 'closed', 'archived') DEFAULT 'active',
    assigned_to BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    last_message_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    closed_by BIGINT UNSIGNED NULL,
    close_reason TEXT,
    unread_messages_count INT DEFAULT 0,
    total_messages_count INT DEFAULT 0,
    tags JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_conversations_company (company_id),
    INDEX idx_conversations_lead (lead_id),
    INDEX idx_conversations_assigned (assigned_to),
    INDEX idx_conversations_status (status),
    INDEX idx_conversations_last_activity (last_activity_at)
);

-- Tabela de mensagens
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    sender ENUM('client', 'agent', 'system') NOT NULL,
    sent_by BIGINT UNSIGNED NULL,
    body TEXT,
    message_type ENUM('text', 'image', 'audio', 'video', 'document') DEFAULT 'text',
    media_url TEXT,
    media_filename VARCHAR(255),
    external_id VARCHAR(255),
    status ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    is_parsed BOOLEAN DEFAULT FALSE,
    parsed_value DECIMAL(10,2),
    parsed_currency VARCHAR(3),
    nlp_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_messages_conversation (conversation_id),
    INDEX idx_messages_company (company_id),
    INDEX idx_messages_sender (sender),
    INDEX idx_messages_created (created_at),
    INDEX idx_messages_external (external_id)
);

-- Tabela de conversões
CREATE TABLE IF NOT EXISTS conversions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'BRL',
    payment_method ENUM('pix', 'boleto', 'cartao_credito', 'cartao_debito', 'transferencia', 'dinheiro', 'outro'),
    detected_by ENUM('manual', 'nlp', 'webhook') NOT NULL,
    detection_data JSON,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at TIMESTAMP NULL,
    detected_at TIMESTAMP NULL,
    notes TEXT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_conversions_company (company_id),
    INDEX idx_conversions_conversation (conversation_id),
    INDEX idx_conversions_status (status),
    INDEX idx_conversions_confirmed (confirmed_at),
    INDEX idx_conversions_value (value)
);

-- Tabela de webhooks
CREATE TABLE IF NOT EXISTS webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    events JSON NOT NULL,
    secret VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    last_triggered_at TIMESTAMP NULL,
    total_triggers INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_webhooks_company (company_id),
    INDEX idx_webhooks_active (active)
);

-- Tabela de links rastreáveis
CREATE TABLE IF NOT EXISTS tracking_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    destination_url TEXT NOT NULL,
    token VARCHAR(32) UNIQUE NOT NULL,
    utm_source VARCHAR(255),
    utm_medium VARCHAR(255),
    utm_campaign VARCHAR(255),
    utm_content VARCHAR(255),
    utm_term VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    click_count INT DEFAULT 0,
    unique_click_count INT DEFAULT 0,
    last_clicked_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tracking_links_company (company_id),
    INDEX idx_tracking_links_token (token),
    INDEX idx_tracking_links_active (active)
);

-- Tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    event VARCHAR(255) NOT NULL,
    auditable_type VARCHAR(255),
    auditable_id BIGINT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_company (company_id),
    INDEX idx_audit_logs_user (user_id),
    INDEX idx_audit_logs_event (event),
    INDEX idx_audit_logs_auditable (auditable_type, auditable_id),
    INDEX idx_audit_logs_created (created_at)
);

-- Tabela de sessões (Laravel)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_last_activity (last_activity)
);

-- Tabela de jobs (Laravel)
CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_jobs_queue (queue)
);

-- Tabela de cache (Laravel)
CREATE TABLE IF NOT EXISTS cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    INDEX idx_cache_expiration (expiration)
);

-- Tabela de tokens de reset de senha
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_password_reset_tokens_email (email)
);

-- Tabela de tokens de acesso (Sanctum)
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_personal_access_tokens_tokenable (tokenable_type, tokenable_id),
    INDEX idx_personal_access_tokens_token (token)
);

-- Inserir dados de exemplo
INSERT INTO companies (id, name, email, phone, trial_expires_at, is_active) VALUES
(1, 'Empresa Demo', 'admin@demo.com', '(11) 99999-9999', DATE_ADD(NOW(), INTERVAL 30 DAY), TRUE);

INSERT INTO users (id, company_id, name, email, password, role, is_active) VALUES
(1, 1, 'Administrador Demo', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company_admin', TRUE),
(2, 1, 'Agente Demo', 'agente@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company_agent', TRUE);

-- Inserir leads de exemplo
INSERT INTO leads (company_id, name, phone, email, origin, status) VALUES
(1, 'Cliente Exemplo 1', '(11) 98888-8888', 'cliente1@exemplo.com', 'meta', 'new'),
(1, 'Cliente Exemplo 2', '(11) 97777-7777', 'cliente2@exemplo.com', 'google', 'contacted'),
(1, 'Cliente Exemplo 3', '(11) 96666-6666', 'cliente3@exemplo.com', 'nao_rastreada', 'qualified');

-- Inserir conversas de exemplo
INSERT INTO conversations (company_id, lead_id, platform, status, started_at, last_activity_at) VALUES
(1, 1, 'whatsapp', 'active', NOW(), NOW()),
(1, 2, 'whatsapp', 'active', NOW(), NOW()),
(1, 3, 'whatsapp', 'closed', NOW(), NOW());

-- Inserir mensagens de exemplo
INSERT INTO messages (conversation_id, company_id, lead_id, sender, body, sent_at) VALUES
(1, 1, 1, 'client', 'Olá, gostaria de saber mais sobre o produto', NOW()),
(1, 1, 1, 'agent', 'Olá! Claro, vou te ajudar com isso.', NOW()),
(2, 1, 2, 'client', 'Qual é o preço?', NOW()),
(2, 1, 2, 'agent', 'O valor é R$ 299,90. Posso te enviar mais detalhes?', NOW());

-- Inserir conversões de exemplo
INSERT INTO conversions (conversation_id, company_id, lead_id, value, detected_by, status, detected_at, confirmed_at) VALUES
(3, 1, 3, 299.90, 'manual', 'confirmed', NOW(), NOW());

COMMIT;

-- Verificar se as tabelas foram criadas
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.tables 
WHERE table_schema = 'visionmetrics' 
ORDER BY TABLE_NAME;




