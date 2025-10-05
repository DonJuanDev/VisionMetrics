# 🔐 VISIONMETRICS - CREDENCIAIS E ACESSOS

**⚠️ IMPORTANTE: Mantenha este arquivo em local seguro e NÃO faça upload para o servidor!**

---

## 📊 INFORMAÇÕES DO SITE

### Domínio
- **URL Principal:** https://visionmetricsapp.com
- **Painel Admin:** https://visionmetricsapp.com/backend/
- **Login:** https://visionmetricsapp.com/backend/login.php
- **Registro:** https://visionmetricsapp.com/backend/register.php

---

## 🗄️ BANCO DE DADOS HOSTINGER

**Onde encontrar:** hPanel > Banco de Dados > MySQL Databases

```
Host: localhost
Porta: 3306
Nome do Banco: u123456789_visionmetrics
Usuário: u123456789_vision
Senha: [PREENCHA AQUI]
```

**phpMyAdmin:** 
- URL: https://visionmetricsapp.com:2083 (ou via hPanel)
- Usuário: u123456789_vision
- Senha: [mesma do banco]

---

## 📁 ACESSO FTP

**Onde encontrar:** hPanel > Arquivos > FTP Accounts

```
Host FTP: ftp.visionmetricsapp.com
Usuário: [PREENCHA AQUI]
Senha: [PREENCHA AQUI]
Porta: 21
```

**Caminho dos arquivos:** 
```
/home/u123456789/domains/visionmetricsapp.com/public_html/
```

---

## 🔑 CHAVES DE SEGURANÇA (.env)

**⚠️ GERE CHAVES ÚNICAS - NÃO USE EXEMPLOS!**

Use este site para gerar: https://www.random.org/strings/

```
JWT_SECRET=[64 caracteres aleatórios]
CSRF_TOKEN_SALT=[64 caracteres diferentes]
```

**Exemplo de como gerar:**
1. Acesse: https://www.random.org/strings/
2. Configure:
   - Number of Strings: 1
   - Length: 64
   - Characters: Alphanumeric
3. Clique em "Get Strings"
4. Copie e cole no .env

---

## 📧 CONFIGURAÇÃO DE EMAIL SMTP

**Onde encontrar:** hPanel > Emails > Manage

### Criar Email

1. Crie: `contato@visionmetricsapp.com`
2. Anote a senha

### Configuração SMTP

```
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=contato@visionmetricsapp.com
SMTP_PASS=[Senha do email]
SMTP_FROM_EMAIL=contato@visionmetricsapp.com
SMTP_FROM_NAME=VisionMetrics
```

**Testador SMTP:** https://www.smtper.net/

---

## 🔌 INTEGRAÇÕES EXTERNAS

### Meta Ads (Facebook/Instagram)

**Onde obter:**
- URL: https://business.facebook.com/events_manager
- Acesse seu Business Manager
- Vá em "Eventos" > "Configurações"

```
META_ADS_ACCESS_TOKEN=[Token de acesso]
META_ADS_PIXEL_ID=[ID do Pixel]
```

**Como gerar token:**
1. https://developers.facebook.com/tools/explorer
2. Selecione seu app
3. Clique em "Generate Access Token"
4. Permissões: `ads_management`, `business_management`

---

### Google Analytics 4

**Onde obter:**
- URL: https://analytics.google.com/
- Acesse sua propriedade GA4
- Admin > Streams de dados > Detalhes do stream

```
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
GA4_API_SECRET=[Criar em "Measurement Protocol API secrets"]
```

**Como gerar API Secret:**
1. No stream de dados, role até "Measurement Protocol API secrets"
2. Clique em "Create"
3. Dê um nome e copie o secret

---

### Stripe (Pagamentos)

**Onde obter:**
- URL: https://dashboard.stripe.com/
- Acesse Developers > API keys

```
STRIPE_PUBLIC_KEY=pk_live_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_live_xxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxx
```

**⚠️ Atenção:**
- Use chaves de **teste** (`pk_test`, `sk_test`) para desenvolvimento
- Use chaves de **produção** (`pk_live`, `sk_live`) apenas quando for ao ar

**Configurar Webhook:**
1. Em Developers > Webhooks
2. Adicione endpoint: `https://visionmetricsapp.com/backend/webhooks/stripe.php`
3. Eventos: `checkout.session.completed`, `invoice.paid`, `customer.subscription.deleted`

---

### TikTok Ads (Opcional)

```
TIKTOK_PIXEL_ID=[ID do Pixel]
TIKTOK_ACCESS_TOKEN=[Token de acesso]
```

---

## 👤 CONTAS DE ADMINISTRADOR

### Conta Principal (Criar ao registrar)

```
Nome: [Seu nome]
Email: seu@email.com
Senha: [Senha forte - mínimo 8 caracteres]
```

### Conta de Teste (Dados seed.sql)

```
Email: admin@visionmetrics.test
Senha: ChangeMe123!
Workspace: Demo Workspace
```

**⚠️ IMPORTANTE:** Apague esta conta após o deploy ou troque a senha!

---

## 🛡️ SEGURANÇA - CHECKLIST

- [ ] Troquei todas as senhas padrão
- [ ] Gerei chaves únicas de JWT e CSRF
- [ ] Configurei `.env` com `APP_DEBUG=false`
- [ ] Instalei certificado SSL (HTTPS)
- [ ] Permissões do `.env` são `644` (não 777!)
- [ ] Apaguei contas de teste/demo
- [ ] Configurei backup automático do banco
- [ ] Testei recuperação de senha por email
- [ ] Ativei rate limiting (`.env`)
- [ ] Revisei logs de erro regularmente

---

## 📱 PRIMEIROS PASSOS APÓS DEPLOY

### 1. Criar Conta Administrativa
```
https://visionmetricsapp.com/backend/register.php
```

### 2. Configurar Workspace
- Nome da empresa
- Logo
- Cores personalizadas

### 3. Gerar API Key
```
Configurações > API Keys > Gerar Nova Chave
```

### 4. Criar Primeira Campanha
```
Campanhas > Links Rastreáveis > Novo Link
```

### 5. Instalar Pixel de Tracking

Cole no `<head>` do seu site:

```html
<script>
(function() {
  var script = document.createElement('script');
  script.src = 'https://visionmetricsapp.com/backend/pixel.php?workspace=SEU_WORKSPACE_ID';
  script.async = true;
  document.head.appendChild(script);
})();
</script>
```

---

## 📊 MONITORAMENTO

### Logs de Erro

**Apache Error Logs:**
- hPanel > Avançado > Error Logs

**Application Logs:**
- Via FTP: `/public_html/logs/app.log`

### Status do Sistema

Crie um arquivo `status.php`:

```php
<?php
require 'backend/config.php';
echo json_encode([
    'status' => 'online',
    'database' => getDB() ? 'connected' : 'error',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
```

Acesse: `https://visionmetricsapp.com/status.php`

### Uptime Monitoring (Recomendado)

Use serviços gratuitos:
- **UptimeRobot:** https://uptimerobot.com/
- **Pingdom:** https://pingdom.com/
- **StatusCake:** https://statuscake.com/

Configure para monitorar: `https://visionmetricsapp.com/backend/healthz.php`

---

## 💾 BACKUP

### Backup do Banco de Dados

**Manual:**
1. phpMyAdmin > Selecione o banco
2. Export > Quick > SQL
3. Baixe o arquivo
4. Guarde em local seguro

**Automático (Hostinger):**
- hPanel > Backups > Configure backups automáticos

**Recomendação:** Backup semanal

### Backup dos Arquivos

**Via FTP:**
- Baixe todas as pastas: `backend/`, `frontend/`, `vendor/`, `uploads/`

**Via cPanel File Manager:**
- Selecione todas as pastas > Compress > Download

**Recomendação:** Backup mensal

---

## 🆘 CONTATOS DE EMERGÊNCIA

### Suporte Hostinger
- **Website:** https://hostinger.com.br/suporte
- **Chat:** Disponível 24/7 no hPanel
- **Email:** suporte@hostinger.com

### Documentação VisionMetrics
- `HOSTINGER_SETUP.md` - Guia completo
- `DEPLOY_CHECKLIST.md` - Checklist
- `LEIA_PRIMEIRO_HOSTINGER.md` - Início rápido

---

## 📝 NOTAS IMPORTANTES

### Renovação SSL
- Renovação automática pelo Let's Encrypt
- Expira a cada 90 dias
- Verifique: hPanel > SSL

### Limites do Plano Hostinger
- **Armazenamento:** [Verifique seu plano]
- **Banco de dados:** Ilimitado (maioria dos planos)
- **Emails:** [Verifique seu plano]
- **Tráfego:** [Verifique seu plano]

### Atualizações
- Sempre teste atualizações em ambiente local
- Faça backup antes de atualizar
- Mantenha o Composer atualizado

---

## ✅ CHECKLIST FINAL DE SEGURANÇA

- [ ] Todas as credenciais foram alteradas
- [ ] `.env` está protegido (não acessível publicamente)
- [ ] HTTPS está ativo
- [ ] Senhas fortes (mínimo 12 caracteres)
- [ ] Autenticação de dois fatores ativada (quando disponível)
- [ ] Logs sendo monitorados
- [ ] Backup configurado
- [ ] Email de recuperação funciona
- [ ] Rate limiting ativo
- [ ] Arquivos sensíveis protegidos pelo `.htaccess`

---

## 📅 MANUTENÇÃO RECOMENDADA

### Semanal
- [ ] Verificar logs de erro
- [ ] Revisar tentativas de login suspeitas
- [ ] Backup do banco de dados

### Mensal
- [ ] Backup completo (arquivos + banco)
- [ ] Atualizar dependências (se houver atualizações)
- [ ] Revisar contas de usuário

### Trimestral
- [ ] Trocar senhas administrativas
- [ ] Revisar integrações ativas
- [ ] Verificar desempenho do servidor
- [ ] Limpar logs antigos

---

## 🎉 TUDO PRONTO!

Com todas estas informações, você tem controle total do seu VisionMetrics!

**Mantenha este arquivo seguro e atualizado!**

---

**Última atualização:** 2025-01-05  
**Versão do Sistema:** 1.0.0  
**Servidor:** Hostinger  
**Domínio:** visionmetricsapp.com


