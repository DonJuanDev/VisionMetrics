# ✅ CHECKLIST DE DEPLOY NO HOSTINGER

Use esta lista para garantir que nada foi esquecido ao fazer o deploy no Hostinger.

---

## 📦 ANTES DO UPLOAD

- [ ] Executei `composer install --no-dev --optimize-autoloader` localmente
- [ ] Pasta `vendor/` está completa e atualizada
- [ ] Criei o arquivo `.env` com as configurações corretas
- [ ] Alterei as credenciais do banco de dados no `.env`
- [ ] Gerei chaves únicas para `JWT_SECRET` e `CSRF_TOKEN_SALT`
- [ ] Configurei `APP_URL=https://visionmetricsapp.com` no `.env`
- [ ] Configurei `APP_ENV=production` e `APP_DEBUG=false`

---

## 🗄️ BANCO DE DADOS

- [ ] Criei o banco de dados no Hostinger
- [ ] Anotei as credenciais (host, nome, usuário, senha)
- [ ] Importei o arquivo `sql/schema.sql` via phpMyAdmin
- [ ] Importei o arquivo `sql/seed.sql` via phpMyAdmin (opcional - dados de exemplo)
- [ ] Verifiquei que as tabelas foram criadas (40+ tabelas)
- [ ] Testei a conexão com o banco via phpMyAdmin

---

## 📁 UPLOAD DOS ARQUIVOS

- [ ] Conectei no FTP ou File Manager do Hostinger
- [ ] Naveguei até `public_html` (ou pasta do domínio)
- [ ] Fiz upload de **TODOS** os arquivos e pastas, exceto:
  - [ ] ❌ `.git/`
  - [ ] ❌ `node_modules/`
  - [ ] ❌ `docker/`
  - [ ] ❌ `docker-compose.yml`
  - [ ] ❌ `Dockerfile`
  - [ ] ❌ `Makefile`
  - [ ] ❌ `reset-docker.*`
- [ ] Confirmei que o arquivo `.env` foi enviado corretamente
- [ ] Confirmei que o arquivo `.htaccess` está na raiz
- [ ] Confirmei que a pasta `vendor/` foi enviada completa

---

## 🔐 PERMISSÕES

- [ ] Ajustei permissão da pasta `logs/` para `755` ou `777`
- [ ] Ajustei permissão da pasta `uploads/` para `755` ou `777`
- [ ] Ajustei permissão do arquivo `.env` para `644`
- [ ] Ajustei permissão do arquivo `.htaccess` para `644`
- [ ] Verifiquei que as permissões não estão muito abertas (segurança)

---

## ⚙️ CONFIGURAÇÃO PHP

- [ ] Selecionei **PHP 8.2** ou superior no hPanel
- [ ] Ativei as extensões necessárias:
  - [ ] `pdo_mysql`
  - [ ] `mysqli`
  - [ ] `mbstring`
  - [ ] `json`
  - [ ] `curl`
  - [ ] `openssl`
- [ ] Ajustei os limites do PHP:
  - [ ] `upload_max_filesize` = `20M`
  - [ ] `post_max_size` = `25M`
  - [ ] `max_execution_time` = `300`
  - [ ] `memory_limit` = `256M`
- [ ] Salvei as configurações

---

## 🔒 SSL E SEGURANÇA

- [ ] Instalei o certificado SSL (Let's Encrypt gratuito)
- [ ] Aguardei a ativação do SSL (5-10 minutos)
- [ ] Testei o acesso via HTTPS
- [ ] Confirmei que o HTTP redireciona para HTTPS automaticamente
- [ ] Verifiquei que o cadeado aparece no navegador

---

## 🧪 TESTES

### Teste 1: Landing Page
- [ ] Acessei `https://visionmetricsapp.com`
- [ ] A landing page carregou corretamente
- [ ] Não há erros no console (F12)
- [ ] CSS e JS estão carregando

### Teste 2: Login
- [ ] Acessei `https://visionmetricsapp.com/backend/login.php`
- [ ] A página de login carregou
- [ ] Não há mensagem de erro de banco de dados

### Teste 3: Registro
- [ ] Acessei `https://visionmetricsapp.com/backend/register.php`
- [ ] Criei uma conta de teste
- [ ] Fui redirecionado para o dashboard
- [ ] O workspace foi criado automaticamente

### Teste 4: Dashboard
- [ ] O dashboard carregou
- [ ] Vejo os cards de métricas
- [ ] O menu lateral funciona
- [ ] Não há erros 500 ou telas brancas

### Teste 5: Funcionalidades
- [ ] Testei criar um link rastreável
- [ ] Testei criar uma API Key
- [ ] Testei o tracking (com curl ou Postman)
- [ ] Testei visualizar leads
- [ ] Testei editar configurações

---

## 📊 MONITORAMENTO

- [ ] Verifiquei os logs de erro do Apache (hPanel → Error Logs)
- [ ] Verifiquei o arquivo `logs/app.log` (deve estar sendo gravado)
- [ ] Configurei alertas de erros (Sentry ou similar - opcional)
- [ ] Testei o sistema em diferentes navegadores
- [ ] Testei o sistema no mobile

---

## 🎨 PERSONALIZAÇÃO (OPCIONAL)

- [ ] Alterei o logo (se necessário)
- [ ] Personalizei as cores no CSS
- [ ] Configurei o email de boas-vindas
- [ ] Ajustei os textos da landing page
- [ ] Configurei o favicon personalizado

---

## 🔌 INTEGRAÇÕES (QUANDO NECESSÁRIO)

- [ ] Configurei Meta Ads CAPI (`META_ADS_ACCESS_TOKEN` no `.env`)
- [ ] Configurei Google Analytics 4 (`GA4_MEASUREMENT_ID` no `.env`)
- [ ] Configurei Stripe para pagamentos (`STRIPE_*` no `.env`)
- [ ] Configurei SMTP para envio de emails
- [ ] Testei cada integração individualmente

---

## 🛡️ SEGURANÇA FINAL

- [ ] Apaguei o arquivo `info.php` (se criei para teste)
- [ ] Verifiquei que o `.env` não é acessível via navegador
- [ ] Verifiquei que os arquivos `.sql` não são acessíveis
- [ ] Verifiquei que a pasta `vendor/` não é listável
- [ ] Ativei rate limiting no `.env`
- [ ] Desabilitei `APP_DEBUG` no `.env` (produção)

---

## 📝 DOCUMENTAÇÃO

- [ ] Li o `HOSTINGER_SETUP.md` completo
- [ ] Anotei as credenciais em local seguro:
  - Banco de dados
  - FTP
  - Email SMTP
  - API Keys de integrações
- [ ] Criei backup dos arquivos `.env` e `.htaccess`
- [ ] Documentei qualquer customização feita

---

## 🚀 PÓS-DEPLOY

- [ ] Anunciei o lançamento para a equipe
- [ ] Criei os primeiros usuários/workspaces
- [ ] Configurei as primeiras campanhas
- [ ] Treinei a equipe no uso do sistema
- [ ] Estabeleci rotina de backup do banco de dados

---

## 🎉 TUDO PRONTO!

Se você marcou todos os itens acima, seu **VisionMetrics** está 100% operacional no Hostinger!

**Data do Deploy:** ___/___/_____

**Deployado por:** _________________

**Observações:**
```
_______________________________________________________

_______________________________________________________

_______________________________________________________
```

---

## 🆘 SE ALGO DEU ERRADO

Consulte a seção **"Solução de Problemas"** no arquivo `HOSTINGER_SETUP.md`.

Principais comandos para debug:
1. Ative `APP_DEBUG=true` no `.env` temporariamente
2. Veja os logs em `logs/app.log`
3. Veja os Error Logs do Apache no hPanel
4. Teste a conexão do banco no phpMyAdmin
5. Verifique permissões das pastas `logs/` e `uploads/`

---

**Boa sorte! 🚀**

