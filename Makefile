.PHONY: help install up down restart build logs shell test lint health clean

# Default target
.DEFAULT_GOAL := help

## help: Mostra esta mensagem de ajuda
help:
	@echo "VisionMetrics - Comandos Disponíveis:"
	@echo ""
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' | sed -e 's/^/ /'

## install: Instalação completa (up + db + composer)
install:
	@echo "🚀 Instalando VisionMetrics..."
	docker-compose up -d
	@echo "⏳ Aguardando MySQL (40s)..."
	@sleep 40
	@echo "📊 Inicializando banco de dados..."
	bash scripts/init_db.sh
	@echo "📦 Instalando dependências PHP..."
	docker-compose exec app composer install
	@echo "✅ Instalação completa!"
	@echo ""
	@echo "🌐 Acessar: http://localhost:3000"
	@echo "📧 Login: admin@visionmetrics.test"
	@echo "🔑 Senha: ChangeMe123!"

## up: Iniciar containers
up:
	docker-compose up -d

## down: Parar containers
down:
	docker-compose down

## restart: Reiniciar containers
restart:
	docker-compose restart

## build: Build containers
build:
	docker-compose build --no-cache

## logs: Ver logs (app)
logs:
	docker-compose logs -f app

## worker-logs: Ver logs do worker
worker-logs:
	docker-compose logs -f worker

## shell: Shell no container app
shell:
	docker-compose exec app sh

## worker-shell: Shell no container worker
worker-shell:
	docker-compose exec worker sh

## db-shell: MySQL shell
db-shell:
	docker-compose exec mysql mysql -u visionmetrics -pvisionmetrics visionmetrics

## composer-install: Instalar dependências PHP
composer-install:
	docker-compose exec app composer install

## test: Rodar testes
test:
	docker-compose exec app vendor/bin/phpunit --testdox

## test-coverage: Testes com coverage
test-coverage:
	docker-compose exec app vendor/bin/phpunit --coverage-html coverage

## lint: Verificar código
lint:
	docker-compose exec app vendor/bin/php-cs-fixer fix --dry-run --diff

## fix: Corrigir código
fix:
	docker-compose exec app vendor/bin/php-cs-fixer fix

## health: Verificar saúde do sistema
health:
	@curl -s http://localhost:3000/backend/healthz.php | jq .

## db-migrate: Rodar migrations
db-migrate:
	bash scripts/init_db.sh

## clean: Limpar tudo (volumes, containers)
clean:
	docker-compose down -v
	rm -rf vendor/ coverage/

## status: Status dos containers
status:
	docker-compose ps