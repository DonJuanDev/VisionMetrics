# 🚀 GUIA COMPLETO: VisionMetrics no Hostinger

Este guia contém **TODOS OS PASSOS** necessários para fazer o VisionMetrics funcionar perfeitamente no Hostinger.

---

## 📋 PRÉ-REQUISITOS

Antes de começar, certifique-se de ter:

✅ Conta ativa no Hostinger com hospedagem web  
✅ Domínio configurado: `visionmetricsapp.com`  
✅ Acesso ao painel hPanel do Hostinger  
✅ Cliente FTP (FileZilla, WinSCP) ou use o File Manager do Hostinger  
✅ Acesso ao phpMyAdmin

---

## 🎯 PASSO 1: PREPARAR O BANCO DE DADOS

### 1.1 Criar o Banco de Dados

1. Entre no **hPanel** do Hostinger
2. Vá em **Banco de Dados** → **MySQL Databases**
3. Clique em **Criar Novo Banco de Dados**
4. Preencha:
   - **Nome do banco**: `visionmetrics`
   - **Nome do usuário**: `vision_user` (ou deixe criar automaticamente)
   - **Senha**: Gere uma senha forte e **anote em algum lugar seguro**

5. **IMPORTANTE**: Anote estas informações:
   ```
   Nome do Banco: u123456789_visionmetrics (exemplo)
   Usuário: u123456789_vision
   Senha: SuaSenhaAqui123!
   Host: localhost
   ```

### 1.2 Importar a Estrutura do Banco

1. No hPanel, clique em **Banco de Dados** → **phpMyAdmin**
2. Selecione o banco de dados que você criou
3. Clique na aba **Import** (Importar)
4. Clique em **Choose File** e selecione o arquivo `sql/schema.sql` do projeto
5. Clique em **Go** (Executar)
6. Aguarde a mensagem de sucesso
7. Repita o processo para o arquivo `sql/seed.sql` (dados de exemplo)

**Atenção**: Se der erro de importação, você pode copiar o conteúdo dos arquivos SQL e colar direto na aba **SQL** do phpMyAdmin.

---

## 📁 PASSO 2: FAZER UPLOAD DOS ARQUIVOS

### 2.1 Preparar os Arquivos Localmente

Antes de fazer upload, você precisa preparar alguns arquivos:

1. **Instalar as dependências do Composer** (se ainda não foi feito):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Criar o arquivo .env**:
   - Copie o conteúdo abaixo e salve como `.env` na raiz do projeto
   
   ```env
   # VisionMetrics - Configuração Hostinger
   APP_NAME=VisionMetrics
   APP_ENV=production
   APP_URL=https://visionmetricsapp.com
   APP_DEBUG=false

   # Database - ALTERE COM SUAS CREDENCIAIS
   DB_HOST=localhost
   DB_NAME=u123456789_visionmetrics
   DB_USER=u123456789_vision
   DB_PASS=SuaSenhaDoB ancoDeDadosAqui
   DB_PORT=3306

   # Redis - Desabilitado
   REDIS_HOST=
   REDIS_PORT=
   REDIS_ENABLED=false

   # Sessions
   SESSION_LIFETIME=7200
   SESSION_SECURE=true
   SESSION_NAME=visionmetrics_session

   # Security - GERE CHAVES ÚNICAS
   # Use: https://www.random.org/strings/ (64 caracteres)
   JWT_SECRET=SUBSTITUA_POR_64_CARACTERES_ALEATORIOS
   CSRF_TOKEN_SALT=SUBSTITUA_POR_OUTRA_STRING_64_CARACTERES

   # Email
   SMTP_HOST=smtp.hostinger.com
   SMTP_PORT=587
   SMTP_USER=contato@visionmetricsapp.com
   SMTP_PASS=SuaSenhaDeEmailAqui
   SMTP_FROM_EMAIL=contato@visionmetricsapp.com
   SMTP_FROM_NAME=VisionMetrics

   # Features
   FEATURE_REAL_TIME=true
   FEATURE_WORKFLOWS=true
   FEATURE_CUSTOM_FIELDS=true

   # Rate Limiting
   RATE_LIMIT_ENABLED=true
   RATE_LIMIT_MAX_REQUESTS=100
   RATE_LIMIT_WINDOW=60

   # Logs
   LOG_LEVEL=INFO
   LOG_CHANNEL=daily
   LOG_PATH=./logs

   # Adapter Mode
   ADAPTER_MODE=simulate

   # Email Verification
   REQUIRE_EMAIL_VERIFICATION=false
   ```

### 2.2 Fazer Upload via FTP ou File Manager

**Opção A: Usando FileZilla ou WinSCP**

1. Conecte-se via FTP:
   - Host: `ftp.visionmetricsapp.com` (ou o que consta no painel)
   - Usuário: Seu usuário FTP do Hostinger
   - Senha: Sua senha FTP
   - Porta: 21

2. Navegue até a pasta `public_html` (ou `domains/visionmetricsapp.com/public_html`)

3. **Faça upload de TODOS os arquivos e pastas do projeto**, EXCETO:
   - ❌ `.git/` (pasta)
   - ❌ `node_modules/` (se existir)
   - ❌ `docker/` (pasta)
   - ❌ `docker-compose.yml`
   - ❌ `Dockerfile`
   - ❌ `Makefile`

**Opção B: Usando o File Manager do Hostinger**

1. No hPanel, vá em **Arquivos** → **Gerenciador de Arquivos**
2. Navegue até `public_html`
3. Clique em **Upload** e selecione os arquivos
4. Ou faça upload de um arquivo ZIP e extraia

**⚠️ IMPORTANTE**: Certifique-se de que o arquivo `.env` foi enviado corretamente!

### 2.3 Verificar Estrutura de Pastas

Após o upload, sua estrutura deve estar assim:

```
public_html/
├── backend/
├── frontend/
├── src/
├── vendor/
├── sql/
├── logs/
├── uploads/
├── .htaccess
├── .env
├── index.php
├── composer.json
└── (outros arquivos)
```

---

## 🔐 PASSO 3: CONFIGURAR PERMISSÕES

### 3.1 Permissões de Pastas e Arquivos

No File Manager ou via FTP, ajuste as permissões:

1. **Pasta `logs/`**: Permissão `755` ou `777` (para escrita)
2. **Pasta `uploads/`**: Permissão `755` ou `777` (para upload de arquivos)
3. **Arquivo `.env`**: Permissão `644` (leitura apenas para o servidor)
4. **Arquivo `.htaccess`**: Permissão `644`

**Como alterar permissões:**
- **FileZilla**: Clique direito → File Permissions
- **File Manager do Hostinger**: Clique direito → Permissions

---

## ⚙️ PASSO 4: CONFIGURAR O PHP

### 4.1 Versão do PHP

1. No hPanel, vá em **Avançado** → **PHP Configuration**
2. Selecione **PHP 8.2** ou superior
3. Salve as alterações

### 4.2 Extensões PHP Necessárias

Certifique-se de que estas extensões estão ativadas:

- ✅ `pdo_mysql`
- ✅ `mysqli`
- ✅ `mbstring`
- ✅ `json`
- ✅ `curl`
- ✅ `openssl`

**Como verificar:**
1. No hPanel → **PHP Configuration**
2. Role até **Extensions**
3. Marque as extensões listadas acima

### 4.3 Ajustar Limites PHP

Na mesma tela de **PHP Configuration**, ajuste:

- `upload_max_filesize`: `20M`
- `post_max_size`: `25M`
- `max_execution_time`: `300`
- `memory_limit`: `256M`

Clique em **Save** (Salvar).

---

## 🧪 PASSO 5: TESTAR O SISTEMA

### 5.1 Testar a Landing Page

Acesse: `https://visionmetricsapp.com`

✅ Você deve ver a landing page do VisionMetrics com:
- Logo e menu
- Seção hero
- Recursos
- Preços
- Botões "Entrar" e "Começar Grátis"

❌ Se der erro, verifique:
- O arquivo `.htaccess` está na raiz?
- HTTPS está forçado (pode dar erro de certificado SSL, veja passo 6)

### 5.2 Testar o Sistema de Login

Acesse: `https://visionmetricsapp.com/backend/login.php`

✅ Você deve ver:
- Página de login do VisionMetrics
- Campo de email e senha
- Link "Esqueci minha senha"

❌ Se der erro "Database connection error":
- Verifique as credenciais no `.env`
- Confirme que o banco foi importado corretamente

### 5.3 Criar um Usuário

Acesse: `https://visionmetricsapp.com/backend/register.php`

1. Preencha:
   - Nome: Seu Nome
   - Email: seu@email.com
   - Senha: senha123 (mínimo 6 caracteres)

2. Clique em **Cadastrar**

3. Você será redirecionado para o dashboard

✅ **SUCESSO!** Se você viu o dashboard, o sistema está funcionando!

---

## 🔒 PASSO 6: CONFIGURAR SSL (HTTPS)

### 6.1 Ativar SSL Gratuito

1. No hPanel, vá em **Segurança** → **SSL**
2. Encontre seu domínio `visionmetricsapp.com`
3. Clique em **Instalar SSL**
4. Escolha **Let's Encrypt** (gratuito)
5. Aguarde a instalação (pode levar 5-10 minutos)

### 6.2 Forçar HTTPS

O arquivo `.htaccess` já está configurado para forçar HTTPS. Após instalar o SSL, teste:

- Acesse: `http://visionmetricsapp.com` (sem S)
- Deve redirecionar automaticamente para `https://visionmetricsapp.com`

---

## 📊 PASSO 7: VERIFICAR FUNCIONALIDADES

### 7.1 Dashboard em Tempo Real

1. Faça login no sistema
2. Vá para **Dashboard**
3. Você deve ver:
   - Cards com métricas
   - Gráficos de conversão
   - Lista de leads
   - Timeline de eventos

### 7.2 Criar um Link Rastreável

1. No menu, vá para **Campanhas** → **Links Rastreáveis**
2. Clique em **Novo Link**
3. Preencha:
   - Nome: Teste Black Friday
   - URL Destino: https://exemplo.com
   - UTM Source: instagram
   - UTM Campaign: blackfriday2024

4. Clique em **Criar**
5. Copie o link curto gerado
6. Teste o link em uma aba anônima

### 7.3 Testar Tracking

1. Vá para **Configurações** → **API Keys**
2. Gere uma nova API Key
3. Copie a chave

4. Teste com curl (ou Postman):
```bash
curl -X POST https://visionmetricsapp.com/backend/track.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "SUA_API_KEY_AQUI",
    "event_type": "page_view",
    "page_url": "https://teste.com",
    "utm_source": "teste"
  }'
```

5. Volte ao dashboard e veja o evento registrado

---

## 🎨 PASSO 8: PERSONALIZAR (OPCIONAL)

### 8.1 Logo e Favicon

1. Substitua o logo em `frontend/assets/` (se tiver)
2. Atualize o favicon no `index.php`

### 8.2 Cores e Tema

Edite `frontend/css/style.css` para alterar:
- Cores primárias
- Fontes
- Espaçamentos

### 8.3 Email de Boas-Vindas

Configure o SMTP no `.env` para enviar emails automáticos.

---

## 🐛 SOLUÇÃO DE PROBLEMAS

### Problema 1: "500 Internal Server Error"

**Causa**: Erro no `.htaccess` ou permissões incorretas

**Solução**:
1. Renomeie o `.htaccess` para `.htaccess_backup`
2. Se o site funcionar, há um erro no `.htaccess`
3. Revise a sintaxe ou use um `.htaccess` mais simples

### Problema 2: "Database connection error"

**Causa**: Credenciais do banco incorretas

**Solução**:
1. Abra o `.env`
2. Verifique se `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` estão corretos
3. Teste a conexão no phpMyAdmin

### Problema 3: Página em branco

**Causa**: Erro de PHP não exibido

**Solução**:
1. Edite o `.env`
2. Mude `APP_DEBUG=false` para `APP_DEBUG=true`
3. Recarregue a página
4. Você verá a mensagem de erro detalhada

### Problema 4: "Class 'Dotenv\Dotenv' not found"

**Causa**: Vendor não foi instalado

**Solução**:
1. No seu computador, execute:
   ```bash
   composer install --no-dev
   ```
2. Faça upload da pasta `vendor/` completa

### Problema 5: CSS/JS não carrega

**Causa**: Caminhos incorretos

**Solução**:
1. Verifique se os arquivos estão em `frontend/css/` e `frontend/js/`
2. Abra o console do navegador (F12) e veja os erros
3. Corrija os caminhos nos arquivos HTML/PHP

### Problema 6: Sessão expira rapidamente

**Causa**: Configuração de sessão

**Solução**:
1. No `.env`, aumente:
   ```
   SESSION_LIFETIME=7200
   ```
2. No PHP Configuration do Hostinger, aumente `session.gc_maxlifetime`

---

## 📞 SUPORTE E AJUDA

### Logs de Erro

Para ver os erros detalhados:

1. **Logs do Apache**: No hPanel → **Avançado** → **Error Logs**
2. **Logs da Aplicação**: Acesse via FTP: `logs/app.log`

### Testar Configuração PHP

Crie um arquivo `info.php` na raiz:

```php
<?php
phpinfo();
?>
```

Acesse: `https://visionmetricsapp.com/info.php`

Você verá todas as configurações do PHP.

**⚠️ IMPORTANTE**: Apague este arquivo depois de verificar!

### Verificar Banco de Dados

1. Acesse o phpMyAdmin
2. Selecione o banco `u123456789_visionmetrics`
3. Verifique se as tabelas foram criadas:
   - `users`
   - `workspaces`
   - `leads`
   - `events`
   - `conversations`
   - (e outras 30+ tabelas)

---

## ✅ CHECKLIST FINAL

Antes de considerar tudo pronto, verifique:

- [ ] Landing page carrega corretamente
- [ ] HTTPS está ativo e forçado
- [ ] Login funciona
- [ ] Registro de novo usuário funciona
- [ ] Dashboard exibe dados
- [ ] Criar link rastreável funciona
- [ ] API de tracking responde
- [ ] Logs estão sendo gravados em `logs/app.log`
- [ ] Emails são enviados (se configurou SMTP)
- [ ] Arquivos sensíveis estão protegidos (.env, .sql)

---

## 🚀 PRÓXIMOS PASSOS

Agora que o sistema está funcionando:

1. **Crie sua primeira campanha**
2. **Configure integrações** (Meta Ads, Google Analytics)
3. **Convide membros da equipe**
4. **Configure webhooks** para automações
5. **Personalize o tema** com suas cores
6. **Configure o Stripe** para aceitar pagamentos

---

## 📖 RECURSOS ADICIONAIS

- **Documentação Completa**: Veja `ARCHITECTURE.md` e `README.md`
- **Guia de Uso**: Veja `GUIA_RAPIDO.txt`
- **Funcionalidades**: Veja `FUNCIONALIDADES_PRONTAS.md`

---

## 🎉 PARABÉNS!

Seu **VisionMetrics** está rodando perfeitamente no Hostinger!

Agora você tem uma plataforma profissional de tracking de leads e atribuição de vendas em produção.

**Dúvidas?** Revise os passos acima ou consulte os logs de erro.

**Boa sorte com suas campanhas! 🚀📊💰**


