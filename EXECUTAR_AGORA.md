# 🚀 EXECUTAR VISIONMETRICS - COMANDOS PRONTOS

## ⚡ EXECUÇÃO RÁPIDA (COPIE E COLE)

### 1️⃣ Primeiro Terminal - Subir Docker

```bash
docker compose up --build
```

**Aguarde ver estas mensagens:**
```
✅ visionmetrics_mysql ... done
✅ visionmetrics_redis ... done  
✅ visionmetrics_php ... done
✅ visionmetrics_worker ... done
✅ visionmetrics_phpmyadmin ... done
```

---

### 2️⃣ Segundo Terminal - Inicializar Banco (IMPORTANTE!)

**Abra um NOVO terminal** e execute:

```bash
docker compose exec php sh scripts/init_db.sh
```

**Você verá:**
```
⏳ Aguardando MySQL estar pronto...
🔧 Inicializando banco de dados...
✅ Banco de dados inicializado com sucesso!
📊 Dados demo carregados!

🔑 Credenciais de acesso:
   Email: admin@visionmetrics.test
   Senha: ChangeMe123!
```

---

### 3️⃣ Acessar o Sistema

**Abra seu navegador:**

🌐 **Aplicação Principal:**
```
http://localhost
```

🗄️ **phpMyAdmin:**
```
http://localhost:8081
```

---

## 🔑 LOGIN

```
Email: admin@visionmetrics.test
Senha: ChangeMe123!
```

---

## ✅ PRONTO! O QUE VOCÊ VAI VER:

### Dashboard
- ✅ 5 Leads de exemplo
- ✅ 5 Conversas do WhatsApp rastreadas
- ✅ 2 Vendas identificadas (R$ 7.500,00 total)
- ✅ Gráficos de atribuição por fonte
- ✅ Timeline de conversas vs vendas
- ✅ Taxa de conversão calculada

### Funcionalidades Disponíveis
- 💬 **Conversas** - Veja conversas do WhatsApp com origem rastreada
- 👥 **Leads** - 5 leads em diferentes etapas do funil
- 💰 **Vendas** - 2 vendas detectadas automaticamente
- 📱 **WhatsApp** - 2 números conectados
- 🔗 **Links** - 3 links rastreáveis de exemplo
- ⚙️ **Configurações** - API Keys, Webhooks, Integrações

---

## 🧪 TESTAR FUNCIONALIDADES

### Teste 1: Ver Conversas
```
Menu > Conversas
```
Filtre por origem, status, busque por nome/telefone

### Teste 2: Criar Link Rastreável
```
Menu > Links > Criar Novo Link
```
Preencha:
- Nome: Minha Campanha
- URL: https://seusite.com
- UTM Source: instagram

Receberá: `http://localhost/l/abc123`

### Teste 3: Testar API de Tracking
```bash
# Pegue uma API Key em: Menu > Configurações
# Depois execute:

curl -X POST http://localhost/track.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "SUA_API_KEY_AQUI",
    "event_type": "page_view",
    "page_url": "https://teste.com",
    "utm_source": "facebook",
    "email": "novoteste@example.com"
  }'
```

### Teste 4: Ver Worker em Ação
```bash
docker compose logs -f worker
```

Verá:
```
[WORKER] Starting VisionMetrics Worker...
[WORKER] Found 2 pending job(s)
[WORKER] Processing job #1
[WORKER] ✅ Job #1 completed
```

---

## 🗄️ EXPLORAR BANCO DE DADOS

**Acesse:** http://localhost:8081

**Login phpMyAdmin:**
```
Servidor: mysql
Usuário: root
Senha: root
Database: visionmetrics
```

**Explore as tabelas:**
- `users` - 1 admin
- `workspaces` - 1 workspace
- `leads` - 5 leads
- `conversations` - 5 conversas
- `messages` - 15+ mensagens
- `sales` - 2 vendas
- `whatsapp_numbers` - 2 números

---

## 🛑 PARAR O SISTEMA

```bash
# Parar sem remover dados
docker compose stop

# Parar e remover containers (mantém dados)
docker compose down

# Remover TUDO (incluindo dados)
docker compose down -v
```

---

## 🔄 REINICIAR/RECRIAR

```bash
# Reiniciar serviços
docker compose restart

# Recriar tudo do zero
docker compose down -v
docker compose up --build
docker compose exec php sh scripts/init_db.sh
```

---

## ❌ PROBLEMAS?

### Porta 80 ocupada
**Erro:** "port 80 already in use"

**Solução:** Edite `docker-compose.yml` linha 28:
```yaml
ports:
  - "8080:80"  # Mude para 8080
```
Acesse: `http://localhost:8080`

### Banco não conecta
```bash
# Aguarde 15 segundos e tente:
docker compose restart php worker
```

### Login não funciona
```bash
# Rode novamente:
docker compose exec php sh scripts/init_db.sh
```

---

## 📊 DADOS DEMO INCLUÍDOS

### Leads (5)
- Maria Silva - Ganho - R$ 2.500
- João Santos - Negociação
- Ana Costa - Qualificado
- Pedro Oliveira - Ganho - R$ 5.000
- Carla Mendes - Novo

### Conversas (5)
- Origem: Google Ads (2)
- Origem: Facebook (1)
- Origem: Instagram (1)
- Origem: Direct (1)

### Vendas (2)
- R$ 2.500 - Google Ads - Black Friday
- R$ 5.000 - Google Ads - Consultoria

### Links Rastreáveis (3)
- promo2024 - 45 cliques
- blackfriday - 120 cliques
- landing - 78 cliques

---

## 🎯 COMANDOS ÚTEIS

```bash
# Ver logs de todos os serviços
docker compose logs -f

# Ver logs apenas do PHP
docker compose logs -f php

# Ver logs apenas do Worker
docker compose logs -f worker

# Entrar no container PHP
docker compose exec php bash

# Entrar no MySQL
docker compose exec mysql mysql -u root -proot visionmetrics

# Status dos containers
docker compose ps

# Reiniciar um serviço específico
docker compose restart php
```

---

## 🎉 ESTÁ TUDO FUNCIONANDO!

✅ **Backend PHP** - 15+ páginas funcionais  
✅ **Frontend** - UI moderna e responsiva  
✅ **Banco MySQL** - 17 tabelas com dados  
✅ **Redis** - Cache funcionando  
✅ **Worker** - Processando jobs  
✅ **phpMyAdmin** - Acesso ao banco  

**Acesse agora:** http://localhost

**Login:** admin@visionmetrics.test / ChangeMe123!

---

## 📱 PRÓXIMOS PASSOS

1. ✅ **Explore o Dashboard** - Veja os gráficos
2. ✅ **Navegue pelas Conversas** - Veja o rastreamento
3. ✅ **Analise as Vendas** - Confira detecção automática
4. ✅ **Crie Links** - Teste criação de links rastreáveis
5. ✅ **Configure API** - Gere sua API Key
6. ✅ **Teste Tracking** - Envie eventos via API

---

**🚀 VisionMetrics está RODANDO e PRONTO PARA USO!**






