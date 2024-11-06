.PHONY: help build up down restart logs ps test install

help: ## Show this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

build: ## Build containers
	docker-compose build

up: ## Start containers
	docker-compose up -d

down: ## Stop containers
	docker-compose down

restart: down up ## Restart containers

logs: ## View container logs
	docker-compose logs -f

ps: ## List containers
	docker-compose ps

test: ## Run tests
	docker-compose exec app php artisan test

install: ## Install dependencies
	docker-compose exec app composer install

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

fresh: ## Fresh migration
	docker-compose exec app php artisan migrate:fresh --seed

cache: ## Clear all cache
	docker-compose exec app php artisan optimize:clear

lint: ## Run code style checks
	docker-compose exec app ./vendor/bin/pint

stan: ## Run static analysis
	docker-compose exec app ./vendor/bin/phpstan analyse
