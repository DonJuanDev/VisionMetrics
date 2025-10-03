# 🚀 COMO RODAR O VISIONMETRICS

## Passo a Passo Simples

### 1️⃣ Subir o Docker

Abra o terminal na pasta do projeto e execute:

```bash
docker compose up --build
```

Aguarde até ver as mensagens:
```
✅ tintimpro_mysql started
✅ tintimpro_redis started
✅ tintimpro_php started
✅ tintimpro_worker started
✅ tintimpro_phpmyadmin started
```

### 2️⃣ Inicializar o Banco de Dados

**Em outro terminal** (mantenha o primeiro rodando), execute:

```bash
docker compose exec php sh scripts/init_db.sh
```

Você verá:
```
⏳ Aguardando MySQL estar pronto...
🔧 Inicializando banco de dados...
✅ Banco de dados inicializado com sucesso!
📊 Dados demo carregados!

🔑 Credenciais de acesso:
   Email: admin@visionmetrics.test
   Senha: ChangeMe123!
```

### 3️⃣ Acessar o Sistema

Abra seu navegador e acesse:

**🌐 Aplicação Principal**
```
http://localhost
```

**🗄️ phpMyAdmin (Gerenciar banco de dados)**
```
http://localhost:8081
```

### 4️⃣ Fazer Login

Use as credenciais:
```
Email: admin@visionmetrics.test
Senha: ChangeMe123!
```

## ✅ O que você verá após o login

### Dashboard
- 3 Leads de exemplo
- 5 Conversas do WhatsApp
- 2 Vendas registradas (R$ 7.500,00 total)
- Gráficos de atribuição
- Timeline de conversas

### Menu Principal
- 📊 **Dashboard** - Visão geral
- 💬 **Conversas** - Conversas do WhatsApp rastreadas
- 👥 **Leads** - Gerenciamento de leads
- 💰 **Vendas** - Relatório de vendas
- 📱 **WhatsApp** - Conectar números
- 🔗 **Links** - Criar links rastreáveis
- ⚙️ **Configurações** - API Keys, Webhooks, Integrações

## 🎯 Testando as Funcionalidades

### 1. Ver Conversas
```
Menu > Conversas
```
Você verá 5 conversas de exemplo com:
- Origem da campanha (Google, Facebook, Instagram)
- Status (ativo, fechado)
- Vendas identificadas automaticamente

### 2. Ver Vendas
```
Menu > Vendas
```
2 vendas de exemplo:
- R$ 2.500,00 - Maria Silva (Google Ads)
- R$ 5.000,00 - Pedro Oliveira (Google Ads)

### 3. Criar um Link Rastreável
```
Menu > Links > Criar Novo Link
```
Preencha:
- Nome: Teste Black Friday
- URL Destino: https://seusite.com
- UTM Source: whatsapp
- UTM Campaign: teste

Você receberá um link curto:
```
http://localhost/l/abc123
```

### 4. Gerar API Key
```
Menu > Configurações > API Keys > Gerar Nova Chave
```

Use a API Key para rastrear eventos:

```bash
curl -X POST http://localhost/track.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "SUA_API_KEY_AQUI",
    "event_type": "page_view",
    "page_url": "https://test.com",
    "utm_source": "teste",
    "email": "teste@example.com"
  }'
```

### 5. Ver Worker Processando Jobs
```bash
docker compose logs -f worker
```

Você verá o worker processando jobs em tempo real:
```
[WORKER] Starting VisionMetrics Worker...
[WORKER] Found 2 pending job(s)
[WORKER] Processing job #1
[WORKER] ✅ Job #1 completed
```

## 🗄️ Explorar o Banco de Dados

Acesse: `http://localhost:8081`

**Login phpMyAdmin:**
```
Servidor: mysql
Usuário: root
Senha: root
```

Selecione database: `visionmetrics`

### Tabelas Principais
- **users** - 1 admin cadastrado
- **workspaces** - 1 workspace demo
- **leads** - 5 leads de exemplo
- **conversations** - 5 conversas
- **messages** - 15+ mensagens
- **sales** - 2 vendas
- **whatsapp_numbers** - 2 números conectados

## 🛠️ Comandos Úteis

### Parar o Sistema
```bash
docker compose down
```

### Reiniciar Tudo
```bash
docker compose restart
```

### Ver Logs
```bash
# Todos os serviços
docker compose logs -f

# Apenas PHP
docker compose logs -f php

# Apenas Worker
docker compose logs -f worker
```

### Recriar o Banco
```bash
docker compose down
docker compose up --build
docker compose exec php sh scripts/init_db.sh
```

## ❌ Problemas Comuns

### "Port 80 already in use"
Outro serviço está usando a porta 80. Opções:
1. Pare o outro serviço (Apache, Nginx, XAMPP, etc)
2. Ou mude a porta no `docker-compose.yml`:
```yaml
ports:
  - "8080:80"  # Acesse em http://localhost:8080
```

### "Cannot connect to database"
Aguarde mais tempo. O MySQL leva ~10-15 segundos para iniciar.

Ou rode:
```bash
docker compose restart php worker
```

### "Login não funciona"
Certifique-se de ter executado:
```bash
docker compose exec php sh scripts/init_db.sh
```

### "Página em branco"
Veja os logs de erro:
```bash
docker compose logs php
```

## 📱 Próximos Passos

1. **Explore o Dashboard**
   - Veja os gráficos interativos
   - Analise as conversas de exemplo

2. **Conecte um WhatsApp**
   - Menu > WhatsApp > Adicionar Número
   - Escaneie o QR Code (modo simulado)

3. **Crie Links Rastreáveis**
   - Use para rastrear suas campanhas
   - Compartilhe no Instagram, WhatsApp, etc

4. **Configure Webhooks**
   - Integre com seu CRM
   - Receba notificações de vendas

5. **Teste a API de Tracking**
   - Adicione o snippet no seu site
   - Veja eventos chegando em tempo real

## 🎉 Tudo Pronto!

Seu **VisionMetrics** está funcionando!

**Acesse**: http://localhost

**Login**: admin@visionmetrics.test / ChangeMe123!

Divirta-se rastreando leads e identificando vendas! 🚀






