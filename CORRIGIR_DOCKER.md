# 🔧 Como Corrigir o Problema do Docker

## ✅ O que foi corrigido:

1. ✅ Adicionadas tabelas faltantes ao `schema.sql`:
   - `conversations` (conversas do WhatsApp)
   - `whatsapp_numbers` (números conectados)
   - `messages` (mensagens)
   - Colunas `type` e `is_active` na tabela `integrations`

2. ✅ Configurado Docker para inicializar automaticamente o banco de dados

3. ✅ Criado arquivo `.env` com configurações padrão

## 🚀 Como Reiniciar o Docker

### Opção 1: Script PowerShell (Recomendado para Windows)
```powershell
.\reset-docker.ps1
```

### Opção 2: Comandos Manuais
```bash
# Parar containers
docker-compose down

# Remover volumes (limpar banco de dados)
docker volume rm teste-vision_mysql_data
docker volume rm teste-vision_redis_data

# Construir e iniciar
docker-compose build --no-cache
docker-compose up -d
```

### Opção 3: Script Bash (Linux/Mac)
```bash
chmod +x reset-docker.sh
./reset-docker.sh
```

## 🌐 URLs de Acesso

- **Frontend/Backend**: http://localhost:3000
- **PHPMyAdmin**: http://localhost:8080
  - Usuário: `visionmetrics`
  - Senha: `visionmetrics`

## 📝 Comandos Úteis

```bash
# Ver logs do container principal
docker-compose logs -f app

# Ver logs do MySQL
docker-compose logs -f mysql

# Status dos containers
docker-compose ps

# Entrar no container
docker-compose exec app bash

# Reiniciar apenas um serviço
docker-compose restart app
```

## 🎯 Primeiro Acesso

1. Acesse: http://localhost:3000/backend/register.php
2. Crie uma conta
3. Faça login
4. O sistema criará automaticamente um workspace

## ⚠️ Se ainda houver problemas

Verifique se as portas estão livres:
```bash
# Windows
netstat -ano | findstr "3000"
netstat -ano | findstr "3307"

# Linux/Mac
lsof -i :3000
lsof -i :3307
```

## 📊 Verificar se o banco está funcionando

Acesse o PHPMyAdmin (http://localhost:8080) e verifique se as tabelas foram criadas:
- users
- workspaces
- leads
- conversations ✅ (nova)
- whatsapp_numbers ✅ (nova)
- messages ✅ (nova)

