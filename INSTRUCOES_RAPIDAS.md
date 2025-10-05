# 🚀 INSTRUÇÕES RÁPIDAS - JÁ ESTÁ TUDO CONFIGURADO!

## ✅ O QUE JÁ FOI FEITO PARA VOCÊ

- ✅ Arquivo `.env` criado com suas credenciais do banco
- ✅ Chaves de segurança geradas automaticamente
- ✅ Sistema configurado para Hostinger
- ✅ Todas as alterações necessárias aplicadas

---

## 📋 VOCÊ SÓ PRECISA FAZER 4 COISAS:

### 1️⃣ RENOMEAR O ARQUIVO .env (10 segundos)

```
Arquivo: .env.production
Renomear para: .env
```

**Como:**
- Windows: Clique direito → Renomear → Apague ".production"
- Resultado final: `.env`

---

### 2️⃣ INSTALAR DEPENDÊNCIAS (1 minuto)

Abra o terminal/PowerShell na pasta do projeto:

```bash
cd "C:\Users\donju\Documents\teste vision"
composer install --no-dev --optimize-autoloader
```

**Aguarde até terminar!** Vai criar/atualizar a pasta `vendor/`

---

### 3️⃣ FAZER UPLOAD VIA FTP (10 minutos)

**Conecte no FTP do Hostinger:**
- Host: `ftp.visionmetricsapp.com`
- Usuário: (seu usuário FTP do Hostinger)
- Senha: (sua senha FTP do Hostinger)

**Faça upload de TUDO para `public_html`:**

Enviar estas pastas/arquivos:
- ✅ `backend/` (pasta completa)
- ✅ `frontend/` (pasta completa)
- ✅ `src/` (pasta completa)
- ✅ `vendor/` (pasta completa - IMPORTANTE!)
- ✅ `sql/` (pasta completa)
- ✅ `logs/` (pasta vazia)
- ✅ `uploads/` (pasta vazia)
- ✅ `mercadopago/` (pasta)
- ✅ `webhooks/` (pasta)
- ✅ `.htaccess` (arquivo)
- ✅ `.env` (arquivo - IMPORTANTE!)
- ✅ `index.php`
- ✅ `composer.json`
- ✅ `composer.lock`
- ✅ `check_hostinger.php` (para verificar depois)

**NÃO enviar:**
- ❌ `.git/`
- ❌ `docker/`
- ❌ `node_modules/`
- ❌ `*.md` (arquivos de documentação)

---

### 4️⃣ IMPORTAR O BANCO DE DADOS (2 minutos)

**Acesse o phpMyAdmin do Hostinger:**
1. No hPanel, vá em: **Banco de Dados → phpMyAdmin**
2. Selecione o banco: `visionmetrics`
3. Clique na aba: **Importar**
4. Escolha o arquivo: `sql/schema.sql` (do seu computador)
5. Clique em: **Executar**
6. Aguarde a mensagem de sucesso ✅

**Opcional - Dados de exemplo:**
7. Repita o processo com: `sql/seed.sql`
8. Isso vai criar um usuário demo e dados de teste

---

## ✅ PRONTO! AGORA TESTE:

### Teste 1: Site Principal
```
https://visionmetricsapp.com
```
Deve mostrar a landing page bonita ✅

### Teste 2: Verificação Automática
```
https://visionmetricsapp.com/check_hostinger.php
```
Deve mostrar tudo VERDE ✅

### Teste 3: Criar sua Conta
```
https://visionmetricsapp.com/backend/register.php
```
Preencha:
- Nome: Seu nome
- Email: seu@email.com
- Senha: sua senha (mínimo 6 caracteres)

Clique em **Cadastrar** → Deve ir para o DASHBOARD ✅

---

## 🎉 SE TUDO FUNCIONAR:

**PARABÉNS! Está rodando! 🚀**

Apague o arquivo de verificação:
```
https://visionmetricsapp.com/check_hostinger.php
```
Delete via FTP ou File Manager.

---

## 🆘 SE DER ERRO:

### Erro: "Database connection error"
**Causa:** Banco não foi importado ou credenciais erradas

**Solução:**
1. Verifique se o arquivo `.env` foi renomeado corretamente
2. Confirme no phpMyAdmin se as tabelas foram criadas
3. Verifique se o banco se chama exatamente: `visionmetrics`

### Erro: "500 Internal Server Error"
**Causa:** Arquivo `.htaccess` com problema

**Solução:**
1. Renomeie `.htaccess` para `.htaccess_backup` via FTP
2. Se funcionar, o problema estava no .htaccess
3. Use o .htaccess original

### Erro: "Class not found"
**Causa:** Pasta `vendor/` não foi enviada

**Solução:**
1. Execute: `composer install --no-dev`
2. Faça upload da pasta `vendor/` completa

### Página em Branco
**Causa:** Erro de PHP não exibido

**Solução:**
1. Edite o `.env` via FTP
2. Mude: `APP_DEBUG=false` para `APP_DEBUG=true`
3. Recarregue a página
4. Você verá o erro detalhado
5. **Volte para false depois!**

---

## 📞 SUAS CREDENCIAIS:

### Banco de Dados MySQL
```
Host: localhost
Nome: visionmetrics
Usuário: visionmetrics
Senha: 182876Jj?
```

### Usuário Demo (se importou seed.sql)
```
Email: admin@visionmetrics.test
Senha: ChangeMe123!
```
**⚠️ Apague esta conta depois ou troque a senha!**

---

## 🎯 CONFIGURAÇÕES EXTRAS (DEPOIS):

### Configurar PHP no Hostinger
1. hPanel → **PHP Configuration**
2. Versão: **PHP 8.2** ou superior
3. Salvar

### Instalar SSL (HTTPS)
1. hPanel → **SSL**
2. Instalar: **Let's Encrypt** (gratuito)
3. Aguardar 5-10 minutos

### Configurar Email SMTP
1. Crie um email: `contato@visionmetricsapp.com`
2. No `.env`, adicione:
```
SMTP_USER=contato@visionmetricsapp.com
SMTP_PASS=senha_do_email
```

---

## 🚀 RESUMO:

1. ✅ Renomear `.env.production` → `.env`
2. ✅ Executar `composer install --no-dev`
3. ✅ Upload via FTP para `public_html`
4. ✅ Importar `sql/schema.sql` no phpMyAdmin
5. ✅ Testar: `https://visionmetricsapp.com`
6. ✅ Criar conta e usar!

**Tempo total: ~15-20 minutos**

---

## 🎉 TUDO CONFIGURADO!

Suas credenciais já estão no arquivo `.env.production`

**Basta fazer o upload e importar o banco!**

**Boa sorte! 🚀📊💰**

