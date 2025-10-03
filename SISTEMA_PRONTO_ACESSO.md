# ✅ Sistema VisionMetrics - PRONTO PARA USO!

## 🎉 Docker Corrigido e Funcionando

Todas as tabelas do banco de dados foram criadas com sucesso!

---

## 🌐 URLs de Acesso

### 🖥️ Sistema Principal
**URL:** http://localhost:3000

### 📊 Páginas Disponíveis:

#### Primeiro Acesso
1. **Registro:** http://localhost:3000/backend/register.php
   - Crie sua conta aqui
   - O sistema criará automaticamente um workspace

2. **Login:** http://localhost:3000/backend/login.php
   - Faça login após o registro

#### Páginas Principais (após login)
- **Dashboard:** http://localhost:3000/backend/dashboard.php
- **Leads/CRM:** http://localhost:3000/backend/leads.php
- **Conversas:** http://localhost:3000/backend/conversations.php
- **WhatsApp:** http://localhost:3000/backend/whatsapp.php
- **Links Rastreáveis:** http://localhost:3000/backend/trackable-links.php
- **Relatórios:** http://localhost:3000/backend/reports.php
- **Jornada de Compra:** http://localhost:3000/backend/journey.php
- **Configurações:** http://localhost:3000/backend/settings.php

### 🗄️ PHPMyAdmin
**URL:** http://localhost:8080
- **Usuário:** visionmetrics
- **Senha:** visionmetrics

---

## ✅ Tabelas Criadas

### Principais:
✅ `users` - Usuários do sistema
✅ `workspaces` - Multi-tenancy
✅ `leads` - CRM de leads
✅ `conversations` - Conversas do WhatsApp ⭐ (NOVA)
✅ `whatsapp_numbers` - Números conectados ⭐ (NOVA)
✅ `messages` - Mensagens ⭐ (NOVA)
✅ `events` - Rastreamento de eventos
✅ `integrations` - Integrações (Meta, GA4)
✅ `tasks` - Tarefas
✅ `tags` - Tags para leads
✅ `custom_fields` - Campos personalizados
✅ `workflows` - Automações

E mais 20+ tabelas auxiliares!

---

## 🚀 Próximos Passos

### 1. Criar sua conta
```
Acesse: http://localhost:3000/backend/register.php
```

### 2. Fazer login
```
Acesse: http://localhost:3000/backend/login.php
```

### 3. Configurar integrações (opcional)
- Meta Ads CAPI
- Google Analytics 4
- WhatsApp Business

---

## 📝 Comandos Úteis

### Ver logs em tempo real:
```powershell
docker-compose logs -f app
```

### Reiniciar um serviço:
```powershell
docker-compose restart app
```

### Parar tudo:
```powershell
docker-compose down
```

### Iniciar tudo:
```powershell
docker-compose up -d
```

### Ver status:
```powershell
docker-compose ps
```

---

## 🐛 Solução de Problemas

### Se a porta 3000 estiver ocupada:
```powershell
# Verificar o que está usando a porta
netstat -ano | findstr "3000"

# Matar o processo (substitua PID pelo número retornado)
taskkill /PID <PID> /F
```

### Se o banco não estiver respondendo:
```powershell
# Reiniciar apenas o MySQL
docker-compose restart mysql

# Ver logs do MySQL
docker-compose logs -f mysql
```

### Limpar tudo e começar do zero:
```powershell
.\reset-docker.ps1
```

---

## 📊 Monitoramento

### Ver uso de recursos:
```powershell
docker stats
```

### Ver containers rodando:
```powershell
docker ps
```

---

## 🎯 Recursos do Sistema

### ✅ Tracking & Analytics
- Pixel de rastreamento
- UTM tracking
- Click IDs (Facebook, Google, TikTok)
- 6 modelos de atribuição

### ✅ CRM
- Gestão de leads
- Kanban board
- Tags e campos customizados
- Timeline de atividades

### ✅ WhatsApp
- Múltiplos números
- Conversas rastreadas
- Atribuição de origem

### ✅ Integrações
- Meta Ads CAPI
- Google Analytics 4
- TikTok Pixel

### ✅ Relatórios
- Exportação CSV/Excel/PDF
- Relatório de GCLID
- Analytics em tempo real

---

## 🔐 Credenciais Padrão

### Banco de Dados (via PHPMyAdmin)
- **Host:** mysql
- **Usuário:** visionmetrics
- **Senha:** visionmetrics
- **Database:** visionmetrics

### Primeiro Usuário
Você criará ao se registrar em:
http://localhost:3000/backend/register.php

---

## 🌟 Pronto para Usar!

Seu sistema está 100% funcional e pronto para uso!

**Acesse agora:** http://localhost:3000

Qualquer dúvida, consulte os arquivos:
- `README.md` - Documentação completa
- `HOW_TO_RUN.md` - Como executar
- `ARCHITECTURE.md` - Arquitetura do sistema
