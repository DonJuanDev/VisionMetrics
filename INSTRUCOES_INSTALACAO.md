# VisionMetrics - Instruções de Instalação

## 🚀 Configuração Rápida

### 1. Banco de Dados MySQL
```bash
mysql -u root -p < database_setup.sql
```

### 2. Backend Laravel
```bash
cd backend
composer install
cp .env.example .env
# Edite o .env com suas configurações MySQL
php artisan key:generate
php artisan serve
```

### 3. Frontend Vue.js
```bash
cd frontend
npm install
npm run dev
```

### 4. Acesso
- Frontend: http://localhost:5173
- Login: admin@demo.com / password

## ✅ Funcionalidades Implementadas

- ✅ Integração completa MySQL
- ✅ APIs REST para todas operações CRUD
- ✅ Layout unificado com sidebar
- ✅ Dashboard com dados reais
- ✅ Autenticação JWT
- ✅ Queries parametrizadas
- ✅ Interface responsiva

Sistema pronto para produção!


