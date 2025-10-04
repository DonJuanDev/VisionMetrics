#!/bin/bash

# Script para reiniciar o Docker com banco de dados limpo

echo "🔄 Parando containers..."
docker-compose down

echo "🗑️  Removendo volumes (limpando banco de dados)..."
docker volume rm teste-vision_mysql_data 2>/dev/null || true
docker volume rm teste-vision_redis_data 2>/dev/null || true

echo "🏗️  Construindo containers..."
docker-compose build --no-cache

echo "🚀 Iniciando containers..."
docker-compose up -d

echo ""
echo "⏳ Aguardando inicialização do banco de dados..."
sleep 15

echo ""
echo "✅ Docker iniciado com sucesso!"
echo ""
echo "📊 Acesse o sistema em: http://localhost:3000"
echo "🗄️  PHPMyAdmin: http://localhost:8080"
echo ""
echo "📝 Para ver os logs:"
echo "   docker-compose logs -f app"
echo ""

