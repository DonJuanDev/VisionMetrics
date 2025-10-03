# VISIONMETRICS - Arquitetura do Sistema

## Visão Geral

VisionMetrics é um SaaS de tracking e atribuição multi-touch para marketing digital, construído com arquitetura moderna e escalável.

## Componentes Principais

```
┌─────────────────────────────────────────────────────────────┐
│                        FRONTEND                              │
│  HTML5 + CSS3 + Vanilla JavaScript + Canvas Charts          │
│  - Dashboard Real-time (SSE)                                 │
│  - CRM Interface (Lista + Kanban)                            │
│  - Configurações e Integrações                               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (PHP 8.2)                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Auth &     │  │   Tracking   │  │   Business   │      │
│  │  Middleware  │  │   Endpoint   │  │    Logic     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
│  - JWT + Sessions                                            │
│  - Multi-tenant (workspace_id)                               │
│  - API REST + Webhooks                                       │
└────────────┬──────────────────────┬─────────────────────────┘
             │                      │
             ▼                      ▼
┌─────────────────────┐  ┌─────────────────────┐
│    MySQL 8.0        │  │    Redis 7          │
│  - 17+ Tabelas      │  │  - Cache            │
│  - Multi-tenant     │  │  - Sessions         │
│  - Indexes          │  │  - Queue            │
└─────────────────────┘  └─────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│                    WORKER (Background Jobs)                  │
│  - Process Meta Ads Conversions                              │
│  - Process GA4 Events                                        │
│  - Process Webhooks                                          │
│  - Retry Logic + Dead Letter                                 │
└─────────────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────┐
│                  INTEGRAÇÕES EXTERNAS                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │Meta Ads  │  │Google    │  │TikTok    │  │ Stripe   │   │
│  │   CAPI   │  │  GA4     │  │  Ads     │  │ Billing  │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Portas e Serviços

| Serviço | Porta | Descrição |
|---|---|---|
| **App (Apache + PHP)** | 3000 | Aplicação principal |
| **MySQL** | 3306 | Banco de dados |
| **Redis** | 6379 | Cache e fila |
| **phpMyAdmin** | 8080 | Interface de gerenciamento DB |

## Fluxo de Dados

### 1. Tracking de Eventos

```
Website do Cliente
        │
        ▼ (JavaScript Tracking Script)
POST /track.php
        │
        ├─> Validação de API Key
        ├─> Criação/Atualização de Lead
        ├─> Armazenamento do Evento
        ├─> Criação de Jobs para Worker
        │
        ▼
Worker processa (async)
        │
        ├─> Meta Ads CAPI (server-side)
        ├─> Google Analytics 4 (Measurement Protocol)
        └─> Webhooks customizados
```

### 2. Atribuição Multi-Touch

```
Conversão acontece
        │
        ▼
Sistema busca todos os touchpoints do lead
        │
        ├─> First Touch
        ├─> Last Touch
        ├─> Linear
        ├─> Time Decay (7 days half-life)
        ├─> Position-Based (40/20/40)
        └─> Last Non-Direct
        │
        ▼
Dashboard mostra valor atribuído por canal
```

### 3. WhatsApp Integration

```
Mensagem recebida → Webhook
        │
        ▼
Sistema cria/atualiza conversa
        │
        ├─> Detecção de venda (IA keywords)
        ├─> Extração de valor
        ├─> Atualização de lead score
        └─> Criação de notificação
```

## Estrutura de Diretórios

```
visionmetrics/
├── backend/                    # Backend PHP
│   ├── *.php                   # Páginas principais
│   ├── views/                  # Templates separados
│   │   └── settings/           # Abas de configurações
│   ├── handlers/               # Lógica de negócio
│   ├── partials/               # Componentes reutilizáveis
│   ├── integrations/           # Classes de integração
│   └── api/                    # Endpoints API
│
├── frontend/                   # Frontend estático
│   ├── css/                    # Estilos
│   │   ├── style.css
│   │   ├── sidebar.css
│   │   └── kanban.css
│   └── js/                     # Scripts
│       ├── app.js
│       ├── charts.js
│       ├── kanban.js
│       └── realtime-dashboard.js
│
├── sql/                        # Database
│   ├── schema.sql              # Schema principal
│   ├── seed.sql                # Dados de exemplo
│   └── migrations/             # Migrations versionadas
│
├── worker/                     # Background jobs
│   ├── process_jobs.php        # Loop principal
│   └── Dockerfile              # Container separado
│
├── scripts/                    # Scripts utilitários
│   ├── init_db.sh              # Inicialização DB
│   └── run_local_checks.sh     # Verificações locais
│
├── tests/                      # Testes PHPUnit
│   ├── AuthTest.php
│   ├── TrackingTest.php
│   └── IntegrationTest.php
│
├── docker/                     # Configs Docker
│   └── apache.conf             # Configuração Apache
│
├── uploads/                    # Arquivos enviados
├── logs/                       # Logs da aplicação
│
├── docker-compose.yml          # Orquestração
├── Dockerfile                  # Build da app
├── Makefile                    # Comandos úteis
├── composer.json               # Dependências PHP
├── phpunit.xml                 # Configuração testes
└── .php-cs-fixer.php           # Lint rules
```

## Tabelas do Banco de Dados

### Core
- `users` - Usuários do sistema
- `workspaces` - Multi-tenancy
- `workspace_members` - Membros por workspace
- `api_keys` - Chaves de API

### Tracking
- `events` - Todos os eventos rastreados
- `leads` - Leads capturados
- `conversations` - Conversas WhatsApp
- `messages` - Mensagens individuais

### Attribution
- `trackable_links` - Links curtos rastreáveis
- `sales` - Vendas registradas

### Integrations
- `integrations` - Configurações de integrações
- `whatsapp_numbers` - Números WhatsApp conectados
- `jobs_log` - Fila de jobs para worker

### SaaS Features
- `subscriptions` - Assinaturas Stripe
- `tags` - Tags para organização
- `lead_tags` - Relacionamento
- `tasks` - Tarefas de CRM
- `notes` - Notas em leads
- `custom_fields` - Campos customizados
- `lead_custom_fields` - Valores dos campos
- `workflows` - Automações
- `notifications` - Notificações
- `audit_log` - Log de auditoria

## Segurança

### Autenticação
- **Sessões** para UI (cookie HttpOnly)
- **JWT** para API (Authorization header)
- Password hashing com bcrypt
- CSRF protection em formulários

### Headers de Segurança
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Content-Security-Policy: ...
```

### Data Protection
- Prepared statements (SQL injection prevention)
- Input sanitization
- Output escaping (XSS prevention)
- Multi-tenant isolation (workspace_id)

## Escalabilidade

### Horizontal Scaling Ready
- Stateless application (sessions no Redis)
- Database read replicas suportado
- Worker pode ter múltiplas instâncias
- Load balancer ready (health checks)

### Caching Strategy
- Redis para sessions
- Redis para rate limiting
- Query result caching (future)

## Observability

### Logs
- Structured JSON logs em `/logs`
- Rotação automática
- Níveis: ERROR, WARNING, INFO, DEBUG

### Monitoring
- `/healthz` - Health check endpoint
- Sentry integration (opcional)
- Métricas de worker (execuções, erros)

### Debugging
- Test endpoints para integrações
- Replay de eventos
- Logs detalhados de jobs

## Integrações Configuradas

### Meta Ads - Conversions API (CAPI)
- Server-side conversion tracking
- Event deduplication
- Test mode support
- Auto-retry on failure

### Google Analytics 4
- Measurement Protocol
- Server-side event tracking
- Client ID + Server Event ID

### TikTok Ads (Template)
- Server-side tracking preparado
- Event API integration

### Stripe Billing
- Subscription management
- Webhook handling
- Plan limits enforcement

## Deploy

### Requisitos
- Docker 20+
- Docker Compose 2+
- 2GB RAM mínimo
- 10GB disk

### Comandos

```bash
# Instalação completa
make install

# Iniciar
make up

# Parar
make down

# Logs
make logs

# Testes
make test

# Migrations
make migrate
```

## Performance

### Otimizações Implementadas
- Multi-stage Docker builds
- Composer autoload optimized
- Apache mod_expires
- Redis caching
- Indexed database queries

### Benchmarks
- Tracking endpoint: <100ms
- Dashboard load: <500ms
- Worker job processing: <1s per job

## Roadmap de Melhoras

### Curto Prazo
- [ ] WebSockets para real-time completo
- [ ] Testes de carga (Apache Bench)
- [ ] CI/CD pipeline local

### Médio Prazo
- [ ] Microservices (API separada)
- [ ] Kubernetes manifests
- [ ] CDN para assets
- [ ] Database sharding

### Longo Prazo
- [ ] GraphQL API
- [ ] Mobile apps
- [ ] Machine Learning para scoring
- [ ] Data warehouse para analytics



