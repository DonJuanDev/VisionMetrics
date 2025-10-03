# VisionMetrics - Guia de Deploy e Configuração

## 🚀 Deploy Local (Desenvolvimento)

### Pré-requisitos
- Docker 20+
- Docker Compose 2+
- ngrok (para testar webhooks localmente)

### Instalação Completa

```bash
# 1. Iniciar containers
docker-compose up -d

# 2. Aguardar MySQL (30-40 segundos)
sleep 40

# 3. Inicializar banco
bash scripts/init_db.sh

# 4. Instalar dependências PHP
docker-compose exec app composer install

# 5. Iniciar worker
docker-compose up -d worker

# 6. Verificar saúde
curl http://localhost:3000/healthz.php
```

### Credenciais Padrão (Seed)
```
Email: admin@visionmetrics.test
Senha: ChangeMe123!
```

---

## 💳 MercadoPago - Configuração

### 1. Criar Conta e Obter Credenciais

1. Acesse: https://www.mercadopago.com.br/developers
2. Crie uma aplicação
3. Vá em: Credentials
4. Copie:
   - **Access Token** (TEST-xxxx para sandbox)
   - **Public Key** (TEST-xxxx para sandbox)

### 2. Configurar .env

```bash
MERCADOPAGO_ACCESS_TOKEN=TEST-1234567890-123456-abcdef...
MERCADOPAGO_PUBLIC_KEY=TEST-12345678-1234-1234-1234-...
MERCADOPAGO_WEBHOOK_TOKEN=seu-token-secreto-aqui
ADAPTER_MODE=simulate
```

### 3. Testar Checkout (Simulate Mode)

```bash
# Com ADAPTER_MODE=simulate, não faz request real
curl -X POST http://localhost:3000/mercadopago/create_preference.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "plan_id=pro"

# Retorna:
# {"success":true,"preference_id":"SIMULATE-xxx","init_point":"..."}
```

### 4. Testar Checkout (Live Mode - Sandbox)

```bash
# Alterar ADAPTER_MODE=live no .env
docker-compose restart app

# Criar preference
curl -X POST http://localhost:3000/mercadopago/create_preference.php \
  -d "plan_id=pro"

# Retorna init_point real do MercadoPago
# Abrir init_point no navegador e completar pagamento com cartão de teste
```

### 5. Cartões de Teste (Sandbox)

```
Aprovado:    5031 4332 1540 6351 (qualquer CVV, data futura)
Recusado:    5031 4332 1540 5431
```

### 6. Configurar Webhook (ngrok para local)

```bash
# Terminal 1: Iniciar ngrok
ngrok http 3000

# Copiar URL: https://xxxxx.ngrok.io

# Terminal 2: Configurar no MercadoPago
# Ir em: https://www.mercadopago.com.br/developers/panel/webhooks
# Adicionar: https://xxxxx.ngrok.io/mercadopago/webhook.php
# Eventos: Payments, Merchant Orders
```

### 7. Testar Webhook Localmente

```bash
# Simular webhook
curl -X POST http://localhost:3000/mercadopago/webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "payment.created",
    "type": "payment",
    "data": {"id": "123456789"}
  }'

# Verificar logs
docker-compose logs app | grep MercadoPago
```

---

## 📱 WhatsApp Cloud API - Configuração

### 1. Criar App Meta

1. Acesse: https://developers.facebook.com/apps
2. Criar App tipo "Business"
3. Adicionar produto: WhatsApp
4. Em Configuration, obter:
   - **Phone Number ID**
   - **WhatsApp Business Account ID**

### 2. Gerar Token Permanente

1. Gerar token temporário em: https://developers.facebook.com/tools/accesstoken/
2. Converter para permanente (ou usar System User Token)
3. Permissões necessárias: `whatsapp_business_messaging`, `whatsapp_business_management`

### 3. Configurar .env

```bash
WHATSAPP_PHONE_ID=123456789012345
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxx...
WHATSAPP_VERIFY_TOKEN=meu-token-secreto-123
WHATSAPP_BUSINESS_ACCOUNT_ID=123456789012345
```

### 4. Configurar Webhook

```bash
# Com ngrok rodando
ngrok http 3000

# No Meta App Dashboard > WhatsApp > Configuration:
# Callback URL: https://xxxxx.ngrok.io/webhooks/whatsapp.php
# Verify Token: meu-token-secreto-123
# Subscribe to: messages
```

### 5. Testar Recebimento

```bash
# Enviar mensagem para o número WhatsApp conectado
# Verificar logs:
docker-compose logs app | grep WhatsApp

# Verificar banco:
docker-compose exec mysql mysql -u visionmetrics -pvisionmetrics visionmetrics \
  -e "SELECT * FROM webhooks_logs WHERE source='whatsapp' ORDER BY received_at DESC LIMIT 5;"
```

### 6. Testar Envio (Simulate)

```bash
# Interface do painel ou via API
# TODO: Adicionar botão de teste no painel
```

---

## 📊 Meta Ads CAPI - Configuração

### 1. Obter Credenciais

1. Acesse: https://developers.facebook.com/tools/accesstoken/
2. Selecione seu App
3. Copie o Access Token
4. Obtenha Pixel ID no Events Manager

### 2. Configurar .env

```bash
META_PIXEL_ID=1234567890123456
META_ACCESS_TOKEN=EAAxxxxxxxxxxxxx...
META_TEST_EVENT_CODE=TEST12345
ADAPTER_MODE=simulate
```

### 3. Testar (Simulate)

```bash
curl -X POST http://localhost:3000/api/test-integrations.php \
  -H "Content-Type: application/json" \
  -d '{"integration":"meta","event":"Lead","email":"test@example.com"}'
```

### 4. Testar (Live - Test Events)

```bash
# Alterar ADAPTER_MODE=live
# Usar META_TEST_EVENT_CODE

# Enviar evento de teste
# Verificar em: Meta Events Manager > Test Events
```

---

## 📈 Google Analytics 4 - Configuração

### 1. Obter Credenciais

1. Google Analytics > Admin > Data Streams
2. Selecionar stream
3. Em "Measurement Protocol API secrets", criar secret
4. Copiar Measurement ID e API Secret

### 2. Configurar .env

```bash
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
GA4_API_SECRET=xxxxxxxxxxxxxxxxxxxx
```

### 3. Testar

```bash
curl -X POST "https://www.google-analytics.com/mp/collect?measurement_id=G-XXXXXXXXXX&api_secret=SECRET" \
  -d '{
    "client_id": "test.123",
    "events": [{
      "name": "page_view",
      "params": {
        "page_location": "https://example.com"
      }
    }]
  }'

# Verificar em: GA4 > Realtime > Events (pode demorar 1-2 min)
```

---

## 🔐 Produção - Checklist

### Antes de Deploy

- [ ] Alterar `APP_ENV=production` no `.env`
- [ ] Alterar `APP_DEBUG=false`
- [ ] Gerar `JWT_SECRET` forte (64 chars)
- [ ] Gerar `CSRF_TOKEN_SALT` forte
- [ ] Configurar `APP_URL` com domínio real
- [ ] Trocar todas as senhas padrão
- [ ] Configurar SMTP real
- [ ] Configurar Sentry DSN (opcional)
- [ ] `ADAPTER_MODE=live` para integrações
- [ ] Configurar backup automático
- [ ] Configurar SSL/HTTPS
- [ ] Habilitar HSTS no `.htaccess`

### Deploy

```bash
# Build otimizado
docker-compose build --no-cache

# Deploy
docker-compose -f docker-compose.prod.yml up -d

# Migrations
bash scripts/init_db.sh

# Verificar
curl https://seu-dominio.com/healthz.php
```

### Monitoramento

```bash
# Logs
docker-compose logs -f app
docker-compose logs -f worker

# Health
watch -n 10 curl https://seu-dominio.com/healthz.php

# Métricas
# TODO: Adicionar Prometheus/Grafana
```

---

## 🧪 Testes

### Rodar Todos os Testes

```bash
docker-compose exec app composer test
```

### Testes Individuais

```bash
docker-compose exec app vendor/bin/phpunit tests/AuthTest.php
docker-compose exec app vendor/bin/phpunit tests/TrackingTest.php
```

### Coverage

```bash
docker-compose exec app composer test:coverage
# Abrir coverage/index.html
```

---

## 🔍 Troubleshooting

### Webhook não recebe eventos

```bash
# Verificar ngrok está rodando
curl https://xxxxx.ngrok.io/healthz.php

# Verificar configuração no Meta/MercadoPago
# Testar manualmente com curl
curl -X POST https://xxxxx.ngrok.io/webhooks/whatsapp.php \
  -d '{"test":"data"}'

# Ver logs
docker-compose logs app | grep webhook
```

### Worker não processa jobs

```bash
# Verificar se está rodando
docker ps | grep worker

# Ver logs
docker-compose logs worker

# Restart
docker-compose restart worker
```

### Integrações retornam erro

```bash
# Verificar credenciais no .env
docker-compose exec app cat /var/www/html/.env | grep META
docker-compose exec app cat /var/www/html/.env | grep GA4

# Testar credenciais
curl -X GET https://graph.facebook.com/v18.0/me?access_token=SEU_TOKEN
```

---

## 📊 Monitoramento de Produção

### Logs Estruturados

```bash
# Logs JSON
tail -f logs/app.log | jq .

# Filtrar por nível
tail -f logs/app.log | jq 'select(.level=="ERROR")'
```

### Alertas Recomendados

- Health check failing (>3 vezes)
- Worker não processando jobs (>5 min)
- Rate limit atingido (>100/min)
- Erros de integração (>10/hora)
- Banco de dados offline

---

**Sistema pronto para produção! 🚀**



