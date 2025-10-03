-- Migrations para adicionar tabelas faltantes

-- Tabela de assinaturas/billing
CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    plan ENUM('free', 'starter', 'pro', 'business') DEFAULT 'free',
    status ENUM('active', 'cancelled', 'past_due', 'trialing') DEFAULT 'active',
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255),
    amount DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'BRL',
    trial_ends_at TIMESTAMP NULL,
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_stripe_customer (stripe_customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de tags
CREATE TABLE IF NOT EXISTS tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#6366F1',
    description TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tag_per_workspace (workspace_id, name),
    INDEX idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de relacionamento lead_tags
CREATE TABLE IF NOT EXISTS lead_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lead_tag (lead_id, tag_id),
    INDEX idx_lead (lead_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de notificações
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_unread (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de notification_rules (configuração de alertas)
CREATE TABLE IF NOT EXISTS notification_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    conditions JSON,
    channels JSON, -- ['email', 'slack', 'webhook']
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de tarefas
CREATE TABLE IF NOT EXISTS tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED,
    assigned_to BIGINT UNSIGNED,
    created_by BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_lead (lead_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de notas
CREATE TABLE IF NOT EXISTS notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_lead (lead_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de campos customizados
CREATE TABLE IF NOT EXISTS custom_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'number', 'date', 'select', 'multiselect', 'boolean', 'url', 'email', 'phone') DEFAULT 'text',
    options JSON, -- Para select/multiselect
    is_required BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    UNIQUE KEY unique_field_key (workspace_id, field_key),
    INDEX idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de valores de campos customizados
CREATE TABLE IF NOT EXISTS lead_custom_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    custom_field_id BIGINT UNSIGNED NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (custom_field_id) REFERENCES custom_fields(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lead_field (lead_id, custom_field_id),
    INDEX idx_lead (lead_id),
    INDEX idx_field (custom_field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de audit log
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED,
    action VARCHAR(50) NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_workspace (workspace_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de automações/workflows
CREATE TABLE IF NOT EXISTS workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workspace_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    trigger_type VARCHAR(50) NOT NULL,
    trigger_config JSON,
    actions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    execution_count INT DEFAULT 0,
    last_executed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de execuções de workflow
CREATE TABLE IF NOT EXISTS workflow_executions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_workflow (workflow_id),
    INDEX idx_status (status),
    INDEX idx_executed (executed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Atualizar tabela de leads para suportar mais campos
ALTER TABLE leads 
ADD COLUMN IF NOT EXISTS company VARCHAR(255) AFTER name,
ADD COLUMN IF NOT EXISTS job_title VARCHAR(255) AFTER company,
ADD COLUMN IF NOT EXISTS website VARCHAR(500) AFTER email,
ADD COLUMN IF NOT EXISTS city VARCHAR(100) AFTER fingerprint,
ADD COLUMN IF NOT EXISTS state VARCHAR(100) AFTER city,
ADD COLUMN IF NOT EXISTS country VARCHAR(100) AFTER state,
ADD COLUMN IF NOT EXISTS timezone VARCHAR(50) AFTER country,
ADD COLUMN IF NOT EXISTS language VARCHAR(10) AFTER timezone,
ADD COLUMN IF NOT EXISTS browser VARCHAR(50) AFTER language,
ADD COLUMN IF NOT EXISTS os VARCHAR(50) AFTER browser,
ADD COLUMN IF NOT EXISTS device_type ENUM('desktop', 'mobile', 'tablet') AFTER os;

-- Atualizar workspaces para billing
ALTER TABLE workspaces
ADD COLUMN IF NOT EXISTS trial_ends_at TIMESTAMP NULL AFTER plan,
ADD COLUMN IF NOT EXISTS billing_email VARCHAR(255) AFTER trial_ends_at;





