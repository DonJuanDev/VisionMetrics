# Como Rodar o VisionMetrics

## 🚀 Instalação Rápida

### Opção 1: Makefile (Recomendado)

```bash
make install
```

Isso irá:
1. Iniciar todos os containers Docker
2. Aguardar MySQL estar pronto
3. Aplicar migrations automaticamente
4. Popular banco com dados de exemplo
5. Mostrar credenciais de acesso

### Opção 2: Manual

```bash
# 1. Copiar variáveis de ambiente
cp env.example .env

# 2. Editar .env com suas credenciais (opcional para desenvolvimento)
nano .env

# 3. Iniciar containers
docker-compose up -d

# 4. Aguardar MySQL (30-40 segundos)
sleep 40

# 5. Aplicar migrations
docker exec -i visionmetrics-mysql mysql -u visionmetrics -pvisionmetrics visionmetrics < sql/schema.sql
docker exec -i visionmetrics-mysql mysql -u visionmetrics -pvisionmetrics visionmetrics < sql/migrations/add_missing_tables.sql

# 6. Aplicar seeds
docker exec -i visionmetrics-mysql mysql -u visionmetrics -pvisionmetrics visionmetrics < sql/seed.sql
```

---

## 🌐 Acessar Sistema

```
Aplicação:  http://localhost:3000
phpMyAdmin: http://localhost:8080
```

### Credenciais Padrão
```
Email:  demo@visionmetrics.com
Senha:  demo123
```

---

## 🔧 Comandos Disponíveis

### Gerenciamento

```bash
make up          # Iniciar todos os serviços
make down        # Parar todos os serviços
make restart     # Reiniciar serviços
make logs        # Ver logs em tempo real
make health      # Verificar saúde
```

### Desenvolvimento

```bash
make test        # Rodar PHPUnit
make lint        # Verificar código
make fix         # Corrigir código automaticamente
make shell       # Shell no container da app
make db          # MySQL CLI
make redis       # Redis CLI
```

### Database

```bash
make migrate     # Aplicar migrations
make seed        # Popular dados de exemplo
```

### Limpeza

```bash
make clean       # Remover tudo (containers + volumes)
make down        # Apenas parar containers
```

---

## 📊 Verificar Status

### Health Check

```bash
# Via Makefile
make health

# OU via curl
curl http://localhost:3000/healthz.php
```

Resposta esperada:
```json
{
  "status": "healthy",
  "timestamp": 1234567890,
  "checks": {
    "database": "ok",
    "redis": "ok",
    "uploads": "ok"
  }
}
```

---

## 🔍 Troubleshooting

### Container não inicia

```bash
# Ver logs
docker-compose logs app

# Rebuild
make build
make up
```

### MySQL não conecta

```bash
# Verificar se está rodando
docker ps

# Ver logs do MySQL
docker logs visionmetrics-mysql

# Aguardar health check
docker-compose ps
```

### Migrations falham

```bash
# Verificar schema
docker exec visionmetrics-mysql mysql -u visionmetrics -pvisionmetrics -e "SHOW DATABASES;"

# Recriar banco
docker exec visionmetrics-mysql mysql -u visionmetrics -pvisionmetrics -e "DROP DATABASE visionmetrics; CREATE DATABASE visionmetrics;"

# Aplicar novamente
make migrate
```

### Worker não processa jobs

```bash
# Ver logs do worker
docker logs visionmetrics-worker

# Restart worker
docker restart visionmetrics-worker

# Verificar Redis
docker exec visionmetrics-redis redis-cli ping
```

---

## 🧪 Rodar Testes

```bash
# Todos os testes
make test

# Teste específico
docker exec visionmetrics-app vendor/bin/phpunit tests/AuthTest.php

# Com coverage
docker exec visionmetrics-app vendor/bin/phpunit --coverage-html coverage/
```

---

## 🛠️ Desenvolvimento

### Instalar dependências PHP

```bash
docker exec visionmetrics-app composer install
```

### Lint e Fix

```bash
# Verificar
make lint

# Corrigir automaticamente
make fix
```

### Acessar Shell

```bash
# Shell do container
make shell

# MySQL CLI
make db

# Redis CLI
make redis
```

---

## 📦 Deploy em Produção

### Preparação

1. **Configurar .env de produção**
```bash
cp env.example .env
nano .env
# Alterar APP_ENV=production
# Configurar credenciais reais
# Adicionar Stripe keys
# Configurar SMTP
```

2. **Build otimizado**
```bash
docker-compose build --no-cache
```

3. **Deploy**
```bash
docker-compose up -d
make migrate
```

4. **Verificar**
```bash
make health
curl https://seu-dominio.com/healthz.php
```

---

## 🔐 Segurança em Produção

### Checklist

- [ ] Alterar todas as senhas padrão
- [ ] Configurar HTTPS (Let's Encrypt)
- [ ] Habilitar HSTS no .htaccess
- [ ] Configurar firewall
- [ ] Configurar backup automático
- [ ] Configurar monitoring (Sentry)
- [ ] Revisar CSP headers
- [ ] Limitar acesso ao phpMyAdmin

---

## 📊 Monitoring

### Logs

```bash
# Ver logs da aplicação
docker logs visionmetrics-app -f

# Ver logs do worker
docker logs visionmetrics-worker -f

# Ver logs do MySQL
docker logs visionmetrics-mysql -f
```

### Métricas

- Health endpoint: `/healthz.php`
- Dashboard stats: `/api/dashboard-stats.php`

---

## 🆘 Suporte

Se encontrar problemas:

1. Verificar logs: `make logs`
2. Verificar health: `make health`
3. Consultar documentação: `/help.php`
4. Abrir ticket: `/support.php`

---

**Sistema pronto! Boa sorte com as vendas! 🚀💰**



