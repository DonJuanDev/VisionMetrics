# 🚀 VisionMetrics - Lead Tracking & Attribution SaaS

**Plataforma profissional de tracking multi-touch e atribuição de vendas para marketing digital**

[![Status](https://img.shields.io/badge/status-production--ready-success)](.)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4)](.)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED)](.)
[![License](https://img.shields.io/badge/license-Proprietary-red)](.)

---

## ✨ Funcionalidades Principais

- 📊 **Dashboard em Tempo Real** - Atualização automática a cada 10s
- 🎯 **Atribuição Multi-Touch** - 6 modelos diferentes (First/Last Touch, Linear, Time Decay, Position-Based, Last Non-Direct)
- 🔌 **Meta Ads CAPI** - Conversões server-side (bypass ad blockers)
- 📈 **Google Analytics 4** - Measurement Protocol integrado
- 📱 **WhatsApp Business** - Tracking automático de conversas
- 🔗 **Links Rastreáveis** - Encurtador + QR Codes
- 💼 **CRM Completo** - Leads, Kanban, Perfil 360°, Tarefas
- ⚡ **Automações** - Workflows com triggers e ações
- 📤 **Exportação Avançada** - CSV, Excel, PDF, GCLID para Google Ads
- 🔒 **LGPD/GDPR** - Cookie consent, export/delete de dados
- 💳 **Multi-Tenant + Billing** - 4 planos configuráveis
- 🏷️ **Tags & Campos Customizados** - Personalização total

---

## 🚀 Quick Start

### Requisitos
- Docker 20+
- Docker Compose 2+
- 2GB RAM mínimo

### Instalação em 1 Comando

```bash
make install
```

**OU manualmente:**

```bash
# 1. Iniciar containers
docker-compose up -d

# 2. Aguardar MySQL estar pronto
sleep 10

# 3. Aplicar migrations
make migrate

# 4. Popular com dados de exemplo
make seed
```

### Acessar

```
🌐 Aplicação: http://localhost:3000
👤 Login: demo@visionmetrics.com
🔑 Senha: demo123
🗄️  phpMyAdmin: http://localhost:8080
```

---

## 📚 Comandos Úteis

```bash
make up          # Iniciar sistema
make down        # Parar sistema
make restart     # Reiniciar
make logs        # Ver logs em tempo real
make test        # Rodar testes PHPUnit
make lint        # Verificar código (PHP-CS-Fixer)
make migrate     # Aplicar migrations
make seed        # Popular dados de exemplo
make health      # Verificar saúde dos serviços
make shell       # Abrir shell no container
make db          # Abrir MySQL CLI
make clean       # Limpar tudo (cuidado!)
```

---

## 📖 Documentação

- [ARCHITECTURE.md](./ARCHITECTURE.md) - Arquitetura detalhada
- [CHANGELOG_AUTOMATIC.md](./CHANGELOG_AUTOMATIC.md) - Histórico de mudanças
- [FINAL_REPORT.md](./FINAL_REPORT.md) - Relatório executivo
- [GUIA_RAPIDO.txt](./GUIA_RAPIDO.txt) - Guia rápido de uso

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.2 (sem frameworks, otimizado)
- **Database:** MySQL 8.0
- **Cache:** Redis 7
- **Frontend:** HTML5 + CSS3 + Vanilla JavaScript
- **Charts:** Canvas API (custom)
- **Infra:** Docker + Docker Compose

---

## 🔐 Segurança

- ✅ Password hashing (bcrypt)
- ✅ Prepared statements (SQL injection prevention)
- ✅ CSRF protection
- ✅ Security headers (CSP, HSTS, X-Frame-Options)
- ✅ Multi-tenant isolation
- ✅ Input sanitization
- ✅ LGPD/GDPR compliant

---

## 🧪 Testes

```bash
# Rodar todos os testes
make test

# Rodar testes específicos
docker exec visionmetrics-app vendor/bin/phpunit tests/AuthTest.php

# Verificar código
make lint
```

**Testes implementados:** 13 testes (Auth, Tracking, Integrations)

---

## 📊 Funcionalidades

### Core
- [x] Tracking de eventos (UTMs, Click IDs, Fingerprint)
- [x] Dashboard em tempo real
- [x] CRM (Leads + Kanban)
- [x] Perfil 360° do lead
- [x] Lead scoring automático

### Integrações
- [x] Meta Ads CAPI
- [x] Google Analytics 4
- [x] WhatsApp tracking
- [x] Webhooks customizados
- [x] API REST

### Ferramentas
- [x] Links rastreáveis
- [x] QR Codes
- [x] Exportação (CSV/Excel/PDF)
- [x] Relatórios customizáveis

### SaaS
- [x] Multi-tenancy
- [x] Planos e limites
- [x] Billing (90% - falta Stripe API)
- [x] API Keys
- [x] Membros por workspace

### Automação
- [x] Workflows básicos
- [x] Tarefas de CRM
- [x] Tags e campos customizados
- [x] Notificações

---

## 💰 Planos Disponíveis

| Plano | Preço | Leads | Eventos | Usuários |
|---|---|---|---|---|
| **Free** | R$ 0/mês | 100 | 1.000 | 1 |
| **Starter** | R$ 97/mês | 1.000 | 10.000 | 3 |
| **Pro** | R$ 297/mês | 10.000 | 100.000 | 10 |
| **Business** | R$ 797/mês | Ilimitado | Ilimitado | Ilimitado |

---

## 🤝 Suporte

- 📧 Email: suporte@visionmetrics.com
- 💬 Chat: Disponível no painel
- 📚 Docs: `/help.php`
- 🐛 Issues: Sistema de tickets interno

---

## 📝 Licença

Proprietary - Todos os direitos reservados.

**© 2024 VisionMetrics. Desenvolvido para comercialização.**

---

## 🎯 Roadmap

### Q4 2024
- [ ] Stripe billing completo
- [ ] Google Ads API
- [ ] Heatmaps viewer
- [ ] Session replay

### Q1 2025
- [ ] Mobile PWA
- [ ] CRM integrations (HubSpot, Salesforce)
- [ ] Chat ao vivo
- [ ] ML scoring

---

**Status: PRONTO PARA COMERCIALIZAR! 🚀**
---

**Maintainer:** [DonJuanDev](https://github.com/DonJuanDev)
