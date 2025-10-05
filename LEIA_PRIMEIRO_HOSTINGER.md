# 🚀 VISIONMETRICS NO HOSTINGER - LEIA PRIMEIRO!

## 👋 Olá!

Preparei **TUDO** para você fazer o VisionMetrics funcionar perfeitamente no Hostinger.

---

## 📚 DOCUMENTAÇÃO CRIADA

Criei 3 arquivos essenciais para você:

### 1. 📘 `HOSTINGER_SETUP.md` ⭐ **PRINCIPAL**
**Guia completo passo a passo** com:
- Como criar o banco de dados
- Como fazer upload dos arquivos
- Como configurar tudo
- Solução de problemas
- **Leia este primeiro!**

### 2. 📋 `DEPLOY_CHECKLIST.md`
**Checklist de deploy** - marque cada item conforme faz:
- Lista de verificação completa
- Não esqueça nada
- Use para garantir que tudo foi feito

### 3. 📄 `ENV_HOSTINGER_EXAMPLE.txt`
**Exemplo do arquivo .env** para copiar:
- Todas as variáveis explicadas
- Copie e cole em um arquivo `.env`
- Altere apenas os valores marcados com ⚠️

---

## ⚡ INÍCIO RÁPIDO (5 PASSOS)

Se você quer começar agora, siga estes 5 passos:

### PASSO 1: Banco de Dados
1. Entre no **hPanel** do Hostinger
2. Crie um banco de dados MySQL
3. Anote: nome, usuário, senha

### PASSO 2: Arquivo .env
1. Abra `ENV_HOSTINGER_EXAMPLE.txt`
2. Copie todo o conteúdo
3. Crie um arquivo chamado `.env` (com ponto no início)
4. Cole o conteúdo
5. Altere as credenciais do banco de dados

### PASSO 3: Upload
1. Conecte via FTP ou File Manager
2. Faça upload de **TODOS** os arquivos para `public_html`
3. Certifique-se de enviar:
   - `.env` ✅
   - `.htaccess` ✅
   - `vendor/` ✅ (pasta completa do Composer)

### PASSO 4: Importar SQL
1. Abra o **phpMyAdmin** no Hostinger
2. Selecione seu banco de dados
3. Clique em **Import**
4. Importe `sql/schema.sql`
5. Importe `sql/seed.sql` (opcional - dados de exemplo)

### PASSO 5: Testar
1. Acesse: `https://visionmetricsapp.com`
2. Vá para: `https://visionmetricsapp.com/backend/register.php`
3. Crie uma conta
4. **Pronto! Está funcionando! 🎉**

---

## 🛠️ O QUE FOI AJUSTADO

Já fiz todas as alterações necessárias para o Hostinger:

✅ **`backend/config.php`**
- Carrega o `.env` manualmente (compatível com Hostinger)
- Conexão com banco de dados ajustada para `localhost`
- Melhor tratamento de erros

✅ **`src/bootstrap.php`**
- Fallback para carregar `.env` sem Composer
- Redis opcional (desabilitado por padrão)
- Configurações de sessão seguras

✅ **`.htaccess`**
- HTTPS forçado (ativado)
- Proteção de arquivos sensíveis
- Headers de segurança
- Rewrite rules para links curtos

✅ **Documentação Completa**
- Guia passo a passo
- Checklist de deploy
- Exemplo de .env comentado

---

## ⚠️ IMPORTANTE - ANTES DE FAZER UPLOAD

### 1. Instalar Dependências do Composer

No seu computador, execute:

```bash
cd "C:\Users\donju\Documents\teste vision"
composer install --no-dev --optimize-autoloader
```

Isso vai criar/atualizar a pasta `vendor/` com todas as dependências.

### 2. Criar o Arquivo .env

**NÃO faça upload sem o .env configurado!**

Use o arquivo `ENV_HOSTINGER_EXAMPLE.txt` como base.

### 3. Configurar as Credenciais

Altere no `.env`:
- `DB_HOST` = `localhost`
- `DB_NAME` = Nome do seu banco (ex: `u123456789_visionmetrics`)
- `DB_USER` = Usuário do banco (ex: `u123456789_vision`)
- `DB_PASS` = Senha do banco
- `APP_URL` = `https://visionmetricsapp.com`
- `JWT_SECRET` = Gere uma chave aleatória de 64 caracteres
- `CSRF_TOKEN_SALT` = Gere outra chave aleatória de 64 caracteres

**Gerar chaves aleatórias:**
Use: https://www.random.org/strings/ (64 caracteres)

---

## 🔍 ESTRUTURA DE ARQUIVOS PARA UPLOAD

Faça upload destas pastas/arquivos:

```
public_html/
├── backend/           ✅ (pasta completa)
├── frontend/          ✅ (pasta completa)
├── src/               ✅ (pasta completa)
├── vendor/            ✅ (pasta completa - OBRIGATÓRIO!)
├── sql/               ✅ (para importar no phpMyAdmin)
├── logs/              ✅ (vazia, será usada)
├── uploads/           ✅ (vazia, será usada)
├── mercadopago/       ✅
├── webhooks/          ✅
├── .htaccess          ✅ (arquivo - IMPORTANTE!)
├── .env               ✅ (arquivo - CRIE ESTE!)
├── index.php          ✅
├── composer.json      ✅
└── composer.lock      ✅
```

**NÃO envie:**
- ❌ `.git/`
- ❌ `node_modules/`
- ❌ `docker/`
- ❌ `docker-compose.yml`
- ❌ `Dockerfile`
- ❌ `Makefile`
- ❌ Arquivos `.md` (opcional - só para documentação)

---

## 🎯 CONFIGURAÇÃO DO PHP NO HOSTINGER

No **hPanel**, vá em **PHP Configuration** e ajuste:

### Versão do PHP
- Selecione: **PHP 8.2** ou **PHP 8.3**

### Extensões (ative estas):
- ✅ `pdo_mysql`
- ✅ `mysqli`
- ✅ `mbstring`
- ✅ `json`
- ✅ `curl`
- ✅ `openssl`

### Limites:
- `upload_max_filesize` = `20M`
- `post_max_size` = `25M`
- `max_execution_time` = `300`
- `memory_limit` = `256M`

---

## 🔒 SSL (HTTPS)

1. No hPanel, vá em **SSL**
2. Instale o **Let's Encrypt** (gratuito)
3. Aguarde 5-10 minutos
4. O `.htaccess` já está configurado para forçar HTTPS

---

## ✅ COMO SABER SE ESTÁ FUNCIONANDO

### Teste 1: Landing Page
Acesse: `https://visionmetricsapp.com`

✅ **Funcionou se:**
- Vê a landing page do VisionMetrics
- Botões "Entrar" e "Começar Grátis" aparecem

❌ **Erro comum:**
- **500 Internal Server Error** → Problema no `.htaccess`
- **404 Not Found** → Arquivos não foram enviados

### Teste 2: Login
Acesse: `https://visionmetricsapp.com/backend/login.php`

✅ **Funcionou se:**
- Vê a página de login

❌ **Erro comum:**
- **Database connection error** → Verifique o `.env`

### Teste 3: Registro
Acesse: `https://visionmetricsapp.com/backend/register.php`

1. Preencha os dados
2. Clique em **Cadastrar**

✅ **Funcionou se:**
- Foi redirecionado para o **Dashboard**
- Vê o menu lateral
- Vê cards de métricas

🎉 **PARABÉNS! Está 100% funcionando!**

---

## 🆘 PROBLEMAS COMUNS

### "500 Internal Server Error"
**Solução:**
1. Renomeie `.htaccess` para `.htaccess_backup`
2. Se funcionar, o problema está no `.htaccess`
3. Verifique a sintaxe

### "Database connection error"
**Solução:**
1. Abra o `.env`
2. Verifique `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
3. Teste a conexão no phpMyAdmin

### "Class not found" ou "Vendor not found"
**Solução:**
1. A pasta `vendor/` não foi enviada
2. Execute `composer install` localmente
3. Faça upload da pasta `vendor/` completa

### Página em branco
**Solução:**
1. Edite o `.env`
2. Mude `APP_DEBUG=false` para `APP_DEBUG=true`
3. Recarregue e veja o erro detalhado

---

## 📞 PRECISA DE AJUDA?

1. **Leia primeiro:** `HOSTINGER_SETUP.md` (guia completo)
2. **Use a checklist:** `DEPLOY_CHECKLIST.md`
3. **Veja os logs:**
   - hPanel → **Error Logs**
   - Arquivo `logs/app.log` (via FTP)

---

## 🎁 BÔNUS: DADOS DE EXEMPLO

Se você importou o `sql/seed.sql`, terá:

**Usuário de teste:**
- Email: `admin@visionmetrics.test`
- Senha: `ChangeMe123!`

**Dados inclusos:**
- 3 leads de exemplo
- 5 conversas do WhatsApp
- 2 vendas registradas
- Links rastreáveis
- Workspace configurado

---

## 📈 PRÓXIMOS PASSOS APÓS O DEPLOY

1. ✅ Criar sua primeira conta real
2. ✅ Configurar integrações (Meta Ads, Google Analytics)
3. ✅ Criar links rastreáveis para suas campanhas
4. ✅ Configurar webhooks
5. ✅ Personalizar o tema (logo, cores)
6. ✅ Configurar Stripe para pagamentos

---

## 📖 DOCUMENTAÇÃO ADICIONAL

- `README.md` - Visão geral do projeto
- `ARCHITECTURE.md` - Arquitetura técnica
- `FUNCIONALIDADES_PRONTAS.md` - Lista de funcionalidades

---

## 🚀 RESUMO FINAL

Para fazer funcionar no Hostinger, você precisa:

1. ✅ Criar banco de dados no Hostinger
2. ✅ Criar arquivo `.env` com credenciais
3. ✅ Executar `composer install` localmente
4. ✅ Fazer upload de todos os arquivos
5. ✅ Importar `sql/schema.sql` no phpMyAdmin
6. ✅ Configurar PHP 8.2 com extensões necessárias
7. ✅ Instalar SSL
8. ✅ Testar o sistema

**Tempo estimado:** 30-60 minutos

---

## 🎉 TUDO PRONTO!

Seu sistema VisionMetrics está pronto para rodar no Hostinger!

**Siga o guia `HOSTINGER_SETUP.md` passo a passo e você terá sucesso!**

Qualquer dúvida, consulte os arquivos de documentação criados.

**Boa sorte com seu deploy! 🚀📊💰**

---

**Criado em:** 2025-01-05  
**Site:** https://visionmetricsapp.com  
**Versão:** 1.0.0

