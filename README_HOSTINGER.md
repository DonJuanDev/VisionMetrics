# 🚀 VISIONMETRICS NO HOSTINGER

## 🎯 INÍCIO RÁPIDO - 5 MINUTOS

### Passo 1: Banco de Dados (2 min)
1. hPanel → **MySQL Databases** → Criar novo
2. Anotar: nome, usuário, senha

### Passo 2: Arquivo .env (1 min)
1. Copiar `ENV_HOSTINGER_EXAMPLE.txt`
2. Criar `.env`
3. Alterar credenciais do banco

### Passo 3: Upload (5 min)
1. FTP ou File Manager
2. Enviar todos os arquivos para `public_html`
3. **Importante:** Enviar pasta `vendor/` completa

### Passo 4: Importar SQL (2 min)
1. phpMyAdmin → Import
2. Importar `sql/schema.sql`
3. Importar `sql/seed.sql` (opcional)

### Passo 5: Testar (30 seg)
1. Acesse: `https://visionmetricsapp.com`
2. Registre uma conta
3. **Pronto! 🎉**

---

## 📚 DOCUMENTAÇÃO COMPLETA

### 🎯 Para Começar (Leia PRIMEIRO!)
- **[LEIA_PRIMEIRO_HOSTINGER.md](LEIA_PRIMEIRO_HOSTINGER.md)** ⭐
  - Visão geral completa
  - Tudo que você precisa saber
  - **Comece por aqui!**

### 📖 Guia Detalhado
- **[HOSTINGER_SETUP.md](HOSTINGER_SETUP.md)**
  - Passo a passo completo
  - Solução de problemas
  - Configurações avançadas

### ✅ Checklist
- **[DEPLOY_CHECKLIST.md](DEPLOY_CHECKLIST.md)**
  - Lista de verificação completa
  - Não esqueça nada
  - Marque cada item

### 🔐 Configurações
- **[ENV_HOSTINGER_EXAMPLE.txt](ENV_HOSTINGER_EXAMPLE.txt)**
  - Exemplo de .env comentado
  - Copie e personalize

- **[CREDENCIAIS_E_ACESSOS.md](CREDENCIAIS_E_ACESSOS.md)**
  - Onde anotar credenciais
  - Configurações de integrações
  - Mantenha seguro!

### 🔍 Verificação
- **[check_hostinger.php](check_hostinger.php)**
  - Script de diagnóstico
  - Verifica se está tudo OK
  - Acesse: `https://visionmetricsapp.com/check_hostinger.php`
  - **Apague após usar!**

---

## 🛠️ REQUISITOS

### Hostinger
- ✅ PHP 8.2 ou superior
- ✅ MySQL 8.0
- ✅ SSL/HTTPS
- ✅ 100MB+ de espaço

### Extensões PHP (ativar no hPanel)
- ✅ `pdo_mysql`
- ✅ `mysqli`
- ✅ `mbstring`
- ✅ `json`
- ✅ `curl`
- ✅ `openssl`

### Local (seu computador)
- ✅ Composer instalado
- ✅ Cliente FTP (FileZilla, WinSCP)

---

## ⚙️ CONFIGURAÇÃO RÁPIDA

### 1. Instalar Dependências
```bash
composer install --no-dev --optimize-autoloader
```

### 2. Criar .env
```bash
# Copie ENV_HOSTINGER_EXAMPLE.txt para .env
# Altere estas linhas:

DB_HOST=localhost
DB_NAME=u123456789_visionmetrics  # Seu banco
DB_USER=u123456789_vision          # Seu usuário
DB_PASS=SuaSenhaAqui               # Sua senha

APP_URL=https://visionmetricsapp.com

JWT_SECRET=GERE_64_CARACTERES_ALEATORIOS
CSRF_TOKEN_SALT=GERE_OUTROS_64_CARACTERES
```

### 3. Upload via FTP
```
Enviar para: public_html/

Incluir:
✅ backend/
✅ frontend/
✅ src/
✅ vendor/
✅ sql/
✅ logs/
✅ uploads/
✅ .htaccess
✅ .env
✅ index.php
✅ composer.json

NÃO enviar:
❌ .git/
❌ node_modules/
❌ docker/
❌ *.md (opcional)
```

### 4. Importar Banco
```
phpMyAdmin > Import > sql/schema.sql
phpMyAdmin > Import > sql/seed.sql (opcional)
```

### 5. Configurar PHP (hPanel)
```
PHP Configuration:
- Versão: PHP 8.2
- upload_max_filesize: 20M
- post_max_size: 25M
- max_execution_time: 300
- memory_limit: 256M
```

### 6. Instalar SSL
```
hPanel > SSL > Instalar Let's Encrypt (gratuito)
Aguardar 5-10 minutos
```

---

## 🧪 TESTAR INSTALAÇÃO

### Teste 1: Verificação Automática
```
https://visionmetricsapp.com/check_hostinger.php
```

**Deve mostrar:**
- ✅ Todas as verificações passaram
- 🎉 Tudo pronto para uso

**Depois apague o arquivo!**

### Teste 2: Landing Page
```
https://visionmetricsapp.com
```

**Deve mostrar:**
- Landing page do VisionMetrics
- Botões funcionando
- CSS carregado

### Teste 3: Login
```
https://visionmetricsapp.com/backend/login.php
```

**Deve mostrar:**
- Tela de login
- Sem erros de banco

### Teste 4: Registro
```
https://visionmetricsapp.com/backend/register.php
```

**Criar conta e verificar:**
- Redirecionamento para dashboard
- Menu lateral funcional
- Workspace criado

---

## 🎨 FUNCIONALIDADES

### ✅ Pronto para Uso
- 📊 Dashboard em tempo real
- 👥 Gestão de leads
- 💬 Tracking de conversas WhatsApp
- 🔗 Links rastreáveis + QR Codes
- 💰 Relatórios de vendas
- 🎯 Atribuição multi-touch
- 📈 Métricas e analytics
- ⚙️ API Keys e webhooks
- 🏷️ Tags e campos customizados
- 📤 Exportação (CSV, Excel, PDF)

### 🔌 Integrações Disponíveis
- Meta Ads (Facebook/Instagram)
- Google Analytics 4
- TikTok Ads
- Stripe (pagamentos)
- WhatsApp Business
- Webhooks customizados

---

## 🆘 PROBLEMAS COMUNS

### "500 Internal Server Error"
**Causa:** Erro no .htaccess

**Solução:**
1. Renomear `.htaccess` para `.htaccess_backup`
2. Se funcionar, revisar sintaxe do .htaccess

### "Database connection error"
**Causa:** Credenciais incorretas

**Solução:**
1. Verificar `.env`: DB_HOST, DB_NAME, DB_USER, DB_PASS
2. Testar conexão no phpMyAdmin

### "Class not found"
**Causa:** Vendor não instalado

**Solução:**
1. Local: `composer install --no-dev`
2. Upload da pasta `vendor/` completa

### Página em branco
**Causa:** Erro de PHP oculto

**Solução:**
1. `.env`: Mudar `APP_DEBUG=true`
2. Ver erro detalhado

### CSS não carrega
**Causa:** Caminhos incorretos

**Solução:**
1. Verificar console do navegador (F12)
2. Conferir estrutura de pastas

---

## 📞 SUPORTE

### Logs de Erro
- **Apache:** hPanel → Error Logs
- **Aplicação:** `logs/app.log` (via FTP)

### Verificar Configuração
1. Criar `info.php`:
```php
<?php phpinfo(); ?>
```
2. Acesse: `https://visionmetricsapp.com/info.php`
3. **Apague depois!**

### Documentação
- `HOSTINGER_SETUP.md` - Guia completo
- `LEIA_PRIMEIRO_HOSTINGER.md` - Início rápido
- `README.md` - Documentação geral

---

## 🔒 SEGURANÇA

### Checklist de Segurança
- [ ] SSL/HTTPS ativo
- [ ] `.env` com permissões 644
- [ ] `APP_DEBUG=false` em produção
- [ ] Chaves JWT e CSRF únicas
- [ ] Senhas fortes
- [ ] Backup configurado
- [ ] Rate limiting ativo

### Arquivos Protegidos
O `.htaccess` já protege:
- ❌ `.env`
- ❌ `*.sql`
- ❌ `*.log`
- ❌ `/vendor`
- ❌ `/sql`

---

## 📊 PRÓXIMOS PASSOS

### 1. Configurar Email SMTP
```
SMTP_HOST=smtp.hostinger.com
SMTP_USER=contato@visionmetricsapp.com
SMTP_PASS=SuaSenha
```

### 2. Configurar Integrações
- Meta Ads para tracking de conversões
- Google Analytics 4 para analytics
- Stripe para pagamentos

### 3. Personalizar
- Logo da empresa
- Cores do tema
- Textos da landing page

### 4. Criar Campanhas
- Links rastreáveis
- QR Codes
- Pixels de tracking

### 5. Convidar Equipe
- Adicionar membros ao workspace
- Definir permissões

---

## 📈 MONITORAMENTO

### Métricas Importantes
- Tempo de resposta
- Taxa de erro
- Uso de disco
- Tráfego mensal

### Ferramentas Recomendadas
- **UptimeRobot** - Monitorar uptime
- **Google Search Console** - SEO
- **Google Analytics** - Visitantes

---

## 💾 BACKUP

### Banco de Dados (Semanal)
```
phpMyAdmin > Export > SQL > Download
```

### Arquivos (Mensal)
```
FTP > Download > backend/, frontend/, vendor/, uploads/
```

### Automático
```
hPanel > Backups > Configurar backup automático
```

---

## 📝 CHANGELOG

### v1.0.0 (2025-01-05)
- ✅ Ajustado para Hostinger
- ✅ Removida dependência de Docker
- ✅ Redis opcional
- ✅ Guias completos de instalação
- ✅ Script de verificação
- ✅ .htaccess otimizado

---

## 🎉 CONCLUSÃO

Seguindo este guia, seu VisionMetrics estará 100% funcional no Hostinger!

**Tempo estimado:** 30-60 minutos

**Dificuldade:** Fácil (com este guia!)

**Suporte:** Consulte os arquivos de documentação

---

## 📚 ÍNDICE DE ARQUIVOS

```
LEIA_PRIMEIRO_HOSTINGER.md    ⭐ Comece aqui!
├── HOSTINGER_SETUP.md         📖 Guia completo
├── DEPLOY_CHECKLIST.md        ✅ Checklist
├── ENV_HOSTINGER_EXAMPLE.txt  🔐 Exemplo de .env
├── CREDENCIAIS_E_ACESSOS.md   📝 Credenciais
├── check_hostinger.php        🔍 Verificação
└── README_HOSTINGER.md        📘 Este arquivo
```

---

**Site:** https://visionmetricsapp.com  
**Versão:** 1.0.0  
**Atualizado:** 2025-01-05  
**Status:** ✅ Pronto para produção

---

**Boa sorte com seu deploy! 🚀📊💰**


