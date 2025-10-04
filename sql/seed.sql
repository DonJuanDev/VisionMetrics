-- ═══════════════════════════════════════════════════════════
-- VisionMetrics - Seed Data
-- Demo data for testing and development
-- ═══════════════════════════════════════════════════════════

SET NAMES utf8mb4;

-- ───────────────────────────────────────────────────────────
-- ADMIN USERS
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `users` (`id`, `email`, `password_hash`, `name`, `email_verified_at`)
VALUES 
(1, 'admin@visionmetrics.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin VisionMetrics', NOW()),
(2, 'juan@admin.com', '$2y$10$eVefn6fOLS3V/VTyhTQSveh0tcO9.G4aUEpT8ZRrWVFIA0vZVHcuS', 'Juan Admin', NOW());

-- ───────────────────────────────────────────────────────────
-- DEMO WORKSPACE
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `workspaces` (`id`, `name`, `slug`, `owner_id`, `plan`, `status`)
VALUES (
  1,
  'Demo Workspace',
  'demo-workspace',
  1,
  'pro',
  'active'
);

INSERT IGNORE INTO `workspace_members` (`workspace_id`, `user_id`, `role`)
VALUES 
(1, 1, 'owner'),
(1, 2, 'admin');

-- ───────────────────────────────────────────────────────────
-- API KEY DE TESTE
-- Key: vm_test_1234567890abcdef
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `api_keys` (`id`, `workspace_id`, `name`, `key_hash`)
VALUES (
  1,
  1,
  'Demo API Key',
  '$2y$10$3K1y7WbwJqYcBvJN5R.PjOGxZJ.1kP8yL9FQMbXfZ7nH2qW5eVxKu' -- vm_test_1234567890abcdef
);

-- ───────────────────────────────────────────────────────────
-- LEADS DE EXEMPLO
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `leads` (
  `id`, `workspace_id`, `email`, `name`, `phone_number`, `company`, 
  `stage`, `score`, `utm_source`, `utm_medium`, `utm_campaign`,
  `gclid`, `first_seen`, `last_seen`
) VALUES
(1, 1, 'joao.silva@example.com', 'João Silva', '+5511999999001', 'Empresa ABC', 
 'qualificado', 85, 'google', 'cpc', 'black-friday-2024', 
 'Cj0KCQiA1234567890', '2024-01-15 10:30:00', '2024-01-16 14:20:00'),

(2, 1, 'maria.santos@example.com', 'Maria Santos', '+5511999999002', 'Startup XYZ', 
 'negociacao', 92, 'facebook', 'paid', 'lancamento-produto', 
 NULL, '2024-01-14 09:15:00', '2024-01-17 11:45:00'),

(3, 1, 'carlos.oliveira@example.com', 'Carlos Oliveira', '+5511999999003', NULL, 
 'novo', 45, 'linkedin', 'organic', NULL, 
 NULL, '2024-01-17 16:30:00', '2024-01-17 16:35:00');

-- ───────────────────────────────────────────────────────────
-- EVENTOS DE EXEMPLO
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `events` (
  `id`, `workspace_id`, `lead_id`, `event_type`, `event_name`,
  `page_url`, `utm_source`, `utm_medium`, `utm_campaign`,
  `user_agent`, `ip_address`, `created_at`
) VALUES
(1, 1, 1, 'pageview', 'Página Inicial', 
 'https://example.com/', 'google', 'cpc', 'black-friday-2024',
 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.100', '2024-01-15 10:30:00'),

(2, 1, 1, 'form_submit', 'Cadastro Newsletter', 
 'https://example.com/newsletter', 'google', 'cpc', 'black-friday-2024',
 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.100', '2024-01-15 10:32:15'),

(3, 1, 2, 'pageview', 'Página de Preços', 
 'https://example.com/pricing', 'facebook', 'paid', 'lancamento-produto',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', '192.168.1.101', '2024-01-14 09:15:00'),

(4, 1, 2, 'custom', 'Iniciou Trial', 
 'https://example.com/trial-started', 'facebook', 'paid', 'lancamento-produto',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', '192.168.1.101', '2024-01-14 09:18:30'),

(5, 1, 3, 'pageview', 'Blog Post', 
 'https://example.com/blog/como-aumentar-conversoes', 'linkedin', 'organic', NULL,
 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '192.168.1.102', '2024-01-17 16:30:00');

-- ───────────────────────────────────────────────────────────
-- ATRIBUIÇÃO DE EXEMPLO
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `attribution_records` (
  `workspace_id`, `lead_id`, `event_id`, `model`, 
  `channel`, `campaign`, `attribution_value`
) VALUES
(1, 1, 1, 'first_touch', 'google_cpc', 'black-friday-2024', 1.00),
(1, 1, 1, 'last_touch', 'google_cpc', 'black-friday-2024', 1.00),
(1, 2, 3, 'first_touch', 'facebook_paid', 'lancamento-produto', 1.00),
(1, 2, 4, 'last_touch', 'facebook_paid', 'lancamento-produto', 1.00);

-- ───────────────────────────────────────────────────────────
-- TAGS DE EXEMPLO
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `tags` (`id`, `workspace_id`, `name`, `color`) VALUES
(1, 1, 'VIP', '#ef4444'),
(2, 1, 'Interessado em Enterprise', '#3b82f6'),
(3, 1, 'Precisa Follow-up', '#f59e0b');

INSERT IGNORE INTO `lead_tags` (`lead_id`, `tag_id`) VALUES
(1, 2),
(2, 1),
(2, 2);

-- ───────────────────────────────────────────────────────────
-- TAREFAS DE EXEMPLO
-- ───────────────────────────────────────────────────────────

INSERT IGNORE INTO `tasks` (
  `workspace_id`, `lead_id`, `assigned_to`, `title`, 
  `status`, `priority`, `due_date`
) VALUES
(1, 2, 1, 'Enviar proposta comercial', 'in_progress', 'high', DATE_ADD(NOW(), INTERVAL 1 DAY)),
(1, 1, 1, 'Agendar demo do produto', 'pending', 'medium', DATE_ADD(NOW(), INTERVAL 2 DAY));

-- ───────────────────────────────────────────────────────────
-- FIM DO SEED
-- ───────────────────────────────────────────────────────────

SELECT 'Seed completed successfully!' as status;