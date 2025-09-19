# VisionMetrics SaaS

Sistema SaaS completo para transformar conversas do WhatsApp em vendas rastreáveis, com atribuição de origem (Meta Ads, Google Ads, UTMs), automações de funil, dashboards e relatórios detalhados.

## 🚀 Funcionalidades Principais

### Core Features
- ✅ **Sistema Multi-tenant** - Isolamento completo de dados por empresa
- ✅ **Trial de 7 dias** - Período de teste automático com bloqueio após expiração
- ✅ **Autenticação JWT/Sanctum** - Sistema de autenticação robusto
- ✅ **Webhook WhatsApp** - Integração completa com WhatsApp Cloud API
- ✅ **Links Rastreáveis** - Sistema completo de tracking com UTM
- ✅ **Attribution Tracking** - Detecção automática de origem (Meta, Google, etc.)
- ✅ **Dashboard Analytics** - Gráficos e métricas em tempo real
- ✅ **Conversões NLP** - Detecção automática de valores e conversões
- ✅ **Sistema de Webhooks** - Notificações para sistemas externos
- ✅ **Exportação de Relatórios** - CSV/XLSX com filtros avançados

### Integrações
- 🔄 **WhatsApp Cloud API** - Recebimento e envio de mensagens
- 🔄 **Meta Conversions API** - Envio de conversões para Facebook/Instagram
- 🔄 **Google Ads API** - Tracking de conversões para Google Ads
- 🔄 **Webhooks Customizáveis** - Integração com qualquer sistema externo

### Segurança (OWASP Compliant)
- 🛡️ **Headers de Segurança** - CSP, HSTS, X-Frame-Options, etc.
- 🛡️ **Rate Limiting** - Proteção contra ataques de força bruta
- 🛡️ **Validação de Input** - Sanitização e validação de todos os dados
- 🛡️ **Logs de Auditoria** - Rastreamento completo de ações sensíveis
- 🛡️ **Criptografia** - Argon2id para senhas, dados sensíveis criptografados

## 🏗️ Arquitetura

### Backend (PHP/Laravel)
- **PHP 8.1+** com Laravel 10
- **MySQL 8.0** para dados relacionais
- **Redis** para cache e sessões
- **Queue Worker** para processamento assíncrono
- **Sanctum** para autenticação API

### Frontend (Vue 3 + Tailwind)
- **Vue 3** com Composition API
- **Pinia** para gerenciamento de estado
- **Vue Router** para navegação
- **Tailwind CSS** para estilização
- **Chart.js** para gráficos e dashboards
- **Vite** como build tool

### Infraestrutura
- **Docker Compose** para desenvolvimento
- **Nginx** como proxy reverso
- **phpMyAdmin** para administração do banco
- **Supervisor** para gerenciar workers
- **Cron** para tarefas agendadas

## 📋 Pré-requisitos

- Docker e Docker Compose
- Git
- Porta 80 e 443 disponíveis (ou configurar outras portas)

## ⚡ Instalação Rápida (5 minutos)

### Pré-requisitos
- Docker e Docker Compose instalados
- Porta 80 e 8080 disponíveis

### 1. Clone e Configure
```bash
# Clone o repositório
git clone https://github.com/seuusuario/visionmetrics.git
cd visionmetrics

# Copie o arquivo de configuração
cp env-example.txt backend/.env
```

### 2. Inicie os Containers
```bash
# Subir todos os serviços
docker-compose up -d

# Aguarde todos os containers iniciarem (2-3 minutos)
docker-compose logs -f
```

### 3. Configure o Banco de Dados
```bash
# Execute as migrations
docker exec -it visionmetrics_app php artisan migrate

# Crie um super admin (opcional)
docker exec -it visionmetrics_app php artisan tinker
```

### 4. Acesse o Sistema
- **🌐 Frontend**: http://localhost
- **🗄️ phpMyAdmin**: http://localhost:8080
  - Usuário: `root`
  - Senha: `root_secure_2024`

### 5. Primeiro Acesso
1. Abra http://localhost
2. Clique em "Registre sua empresa"
3. Preencha os dados da sua empresa
4. Faça login e explore o sistema!

## 🔧 Configuração Detalhada

### WhatsApp Cloud API

1. **Crie uma aplicação no Meta for Developers**
2. **Configure o Webhook URL**: `https://seu-dominio.com/api/webhooks/whatsapp`
3. **Defina o Verify Token** no arquivo `.env`
4. **Configure as permissões necessárias**: `whatsapp_business_messaging`

### Configuração de Domínio

Para produção, configure seu domínio no nginx:

```nginx
# deploy/nginx/conf.d/default.conf
server_name seu-dominio.com www.seu-dominio.com;
```

### SSL/TLS (Produção)

Para habilitar HTTPS com Let's Encrypt:

```bash
# Instale o certbot no container nginx
docker exec -it visionmetrics_nginx certbot --nginx -d seu-dominio.com
```

## 📚 Guia Completo de Uso

### 🚀 Primeiros Passos

#### 1. Registro e Login
1. **Criar Empresa**: Acesse http://localhost e clique em "Registre sua empresa"
2. **Preencher Dados**: Nome da empresa, seu nome, email, WhatsApp, senha
3. **Trial Gratuito**: Automaticamente ativado por 7 dias
4. **Primeiro Login**: Use o email e senha cadastrados

#### 2. Visão Geral do Dashboard
- **Cards de Estatísticas**: Conversas, leads rastreados, conversões, receita
- **Gráficos**: Distribuição por origem (Meta Ads, Google Ads, etc.)
- **Menu Lateral**: Acesso a todas as funcionalidades
- **Trial Status**: Dias restantes no topo

### 📱 Configuração WhatsApp (ESSENCIAL)

#### Passo 1: Meta for Developers
1. Acesse [developers.facebook.com](https://developers.facebook.com)
2. Crie um App Business → WhatsApp Business Platform
3. Vá em WhatsApp → Configuração → Webhook
4. **URL do Webhook**: `https://seu-dominio.com/api/webhooks/whatsapp`
5. **Token de Verificação**: `meu_webhook_token_secreto_2024` (do .env)
6. **Subscrever eventos**: messages, message_status

#### Passo 2: Tokens e Configuração
1. Copie o **Token de Acesso** e **Phone Number ID**
2. Edite `backend/.env`:
```env
WHATSAPP_CLOUD_API_TOKEN=seu_token_aqui
WHATSAPP_PHONE_NUMBER_ID=seu_phone_number_id
```
3. Reinicie: `docker-compose restart app`

#### Passo 3: Teste
1. Envie uma mensagem para o número WhatsApp Business
2. Verifique em "Conversas" se apareceu
3. ✅ **Sucesso!** Agora todas as mensagens chegam automaticamente

### 🔗 Links Rastreáveis

#### Criar Link
1. **Menu** → Links Rastreáveis → **Novo Link**
2. **Nome**: Ex: "Campanha Facebook Maio"
3. **URL Destino**: Sua landing page
4. **UTM Parameters**:
   - Source: `facebook`
   - Campaign: `maio2024`
   - Medium: `social`

#### Uso do Link
1. **Copie o link gerado**: `http://localhost/r/ABC123`
2. **Use em campanhas**: Facebook Ads, Instagram, Google Ads
3. **QR Code**: Disponível para impressos
4. **Tracking**: Visitantes são automaticamente rastreados

#### Como Funciona
1. Cliente clica no link rastreável
2. É redirecionado para sua landing page
3. Dados ficam salvos em cookie
4. Quando cliente manda mensagem no WhatsApp → **origem é identificada!**

### 💬 Gestão de Conversas

#### Visualizar Conversas
- **Menu** → Conversas
- **Filtros**: Por origem, status, responsável, data
- **Status**: Aberta, Fechada, Qualificada, Convertida, Perdida

#### Atribuir Responsável
1. Abra uma conversa
2. Clique em "Atribuir"
3. Selecione um agente da equipe
4. Agente recebe notificação

#### Marcar como Conversão
1. Na conversa, clique **"Marcar Conversão"**
2. Digite o valor da venda: `1500.00`
3. Método de pagamento: PIX, Boleto, etc.
4. **Automático**: Sistema já detecta valores nas mensagens!

### 📈 Relatórios e Análises

#### Dashboard Principal
- **Conversas por Origem**: Gráfico de barras por dia
- **Taxa de Rastreamento**: % de leads com origem identificada
- **Taxa de Conversão**: % de leads que viraram venda
- **Receita Total**: Soma de todas as conversões

#### Relatórios Detalhados
1. **Menu** → Relatórios
2. **Tipos**:
   - **Performance**: Por origem, por agente, diário
   - **Atribuição**: Análise UTM, modelos de atribuição
   - **Funil**: Leads → Contato → Qualificado → Convertido

#### Exportar Dados
1. **Botão "Baixar Relatório"** em qualquer tela
2. **Formato**: CSV ou Excel
3. **Filtros**: Período, origem, status
4. **Uso**: Análises externas, apresentações

### 👥 Gerenciar Equipe

#### Adicionar Usuários (apenas Admins)
1. **Menu** → Usuários → **Novo Usuário**
2. **Papéis**:
   - **Admin**: Controle total
   - **Agente**: Gerenciar conversas e conversões
   - **Visualizador**: Apenas visualizar relatórios

#### Controle de Acesso
- **Multi-tenant**: Cada empresa vê apenas seus dados
- **Logs de Auditoria**: Todas as ações ficam registradas
- **Sessões**: Controle de login/logout

### 🔌 Integrações Avançadas

#### Webhooks Personalizados
1. **Menu** → Webhooks → **Novo Webhook**
2. **URL**: Endpoint do seu sistema
3. **Eventos**: Nova conversa, conversão, etc.
4. **Formato**: JSON com dados da conversa/lead

#### Meta Conversions API
- Envia conversões automaticamente para Facebook
- Melhora otimização das campanhas
- Configure tokens no menu Configurações

#### Google Ads
- Tracking de conversões automático
- ROAS real das campanhas
- Configure API keys nas configurações

### ⚙️ Configurações da Empresa

#### Informações Básicas
- Nome, CNPJ, telefone de suporte
- Timezone para relatórios
- Configurações de notificação

#### Integrações
- **WhatsApp**: Status da conexão, teste webhook
- **Meta Ads**: Configurar tokens, pixel ID
- **Google Ads**: Cliente ID, tokens de acesso

### 🔒 Segurança e Backup

#### Recursos de Segurança
- **2FA**: Autenticação de dois fatores (recomendado)
- **Rate Limiting**: Proteção contra ataques
- **Logs**: Auditoria completa de ações
- **Sessões**: Timeout automático

#### Backup de Dados
- **Banco**: Backup automático diário
- **Exportação**: Dados sempre exportáveis
- **LGPD**: Conformidade com privacidade

## 🧪 Testes

### Testes Backend (PHPUnit)
```bash
docker exec -it visionmetrics_app php artisan test
```

### Testes Frontend (Jest)
```bash
cd frontend
npm run test
```

### Testes E2E (Cypress)
```bash
cd frontend
npm run test:e2e
```

## 📈 Monitoramento

### Health Checks
- **API Health**: http://localhost/api/health
- **Metrics**: http://localhost/api/metrics

### Logs
```bash
# Application logs
docker logs visionmetrics_app

# Nginx logs  
docker logs visionmetrics_nginx

# Database logs
docker logs visionmetrics_db
```

## 🔒 Segurança

### Checklist de Produção

- [ ] Altere todas as senhas padrão
- [ ] Configure HTTPS com certificado válido
- [ ] Configure firewall para portas específicas
- [ ] Configure backup automático do banco
- [ ] Configure monitoramento de logs
- [ ] Atualize regularmente as dependências
- [ ] Configure alertas para erros críticos

### Headers de Segurança

O sistema implementa automaticamente:
- Content Security Policy (CSP)
- HTTP Strict Transport Security (HSTS)
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection

## 🗄️ Estrutura do Banco de Dados

### Principais Tabelas

- `companies` - Dados das empresas (tenants)
- `users` - Usuários do sistema
- `leads` - Leads/contatos rastreados
- `conversations` - Conversas do WhatsApp
- `messages` - Mensagens individuais
- `conversions` - Conversões detectadas/confirmadas
- `tracking_links` - Links rastreáveis criados
- `webhooks` - Webhooks configurados
- `audit_logs` - Logs de auditoria

## 🚀 Deploy em Produção

### Opção 1: Servidor Próprio (Ubuntu/CentOS)

1. **Instale o Docker**
2. **Clone e configure o projeto**
3. **Configure DNS para apontar para o servidor**
4. **Configure SSL com Let's Encrypt**
5. **Configure backup automático**

### Opção 2: Cloud (AWS/DigitalOcean/Hetzner)

1. **Crie uma instância com Docker**
2. **Configure Load Balancer (se necessário)**
3. **Configure banco de dados gerenciado (RDS/etc)**
4. **Configure Redis gerenciado**
5. **Configure backup automático**

## 🤝 Suporte

### Documentação da API
Acesse `/api/docs` para ver a documentação completa da API (Swagger/OpenAPI).

### Logs de Depuração
```bash
# Ver logs em tempo real
docker-compose logs -f app

# Ver logs específicos
docker-compose logs nginx
docker-compose logs db
```

### Problemas Comuns

#### WhatsApp não recebe mensagens
1. Verifique se o webhook está configurado corretamente
2. Confirme se o verify token está correto
3. Teste o endpoint de webhook diretamente

#### Erro de permissões
```bash
# Ajuste as permissões dos arquivos
docker exec -it visionmetrics_app chown -R www-data:www-data storage
docker exec -it visionmetrics_app chmod -R 775 storage
```

#### Performance lenta
1. Configure Redis para cache
2. Otimize consultas do banco
3. Configure CDN para assets estáticos

## 📝 Licença

Este projeto é proprietário. Entre em contato para informações sobre licenciamento.

---

## 📍 Logo
Para adicionar sua logo, coloque o arquivo PNG em:
- **Frontend**: `frontend/src/assets/logo.png`
- **Favicon**: Substitua `frontend/public/favicon.ico`

A logo será automaticamente utilizada no header da aplicação e nos emails.

---

**VisionMetrics** - Transforme conversas em vendas rastreáveis 🚀
