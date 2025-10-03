# Script para reiniciar o Docker com banco de dados limpo

Write-Host "🔄 Parando containers..." -ForegroundColor Yellow
docker-compose down

Write-Host "🗑️  Removendo volumes (limpando banco de dados)..." -ForegroundColor Yellow
docker volume rm teste-vision_mysql_data -ErrorAction SilentlyContinue
docker volume rm teste-vision_redis_data -ErrorAction SilentlyContinue

Write-Host "🏗️  Construindo containers..." -ForegroundColor Cyan
docker-compose build --no-cache

Write-Host "🚀 Iniciando containers..." -ForegroundColor Green
docker-compose up -d

Write-Host ""
Write-Host "⏳ Aguardando inicialização do banco de dados..." -ForegroundColor Cyan
Start-Sleep -Seconds 15

Write-Host ""
Write-Host "✅ Docker iniciado com sucesso!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Acesse o sistema em: http://localhost:3000" -ForegroundColor Cyan
Write-Host "🗄️  PHPMyAdmin: http://localhost:8080" -ForegroundColor Cyan
Write-Host ""
Write-Host "📝 Para ver os logs:" -ForegroundColor Yellow
Write-Host "   docker-compose logs -f app" -ForegroundColor White
Write-Host ""
