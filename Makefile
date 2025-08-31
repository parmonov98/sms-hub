.PHONY: help build up down restart logs shell composer artisan migrate fresh seed test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker compose build

up: ## Start Docker containers
	docker compose up -d

down: ## Stop Docker containers
	docker compose down

restart: ## Restart Docker containers
	docker compose restart

logs: ## Show container logs
	docker compose logs -f

shell: ## Access app container shell
	docker compose exec app bash

composer: ## Run composer command (usage: make composer cmd="require package")
	docker compose exec app composer $(cmd)

artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	docker compose exec app php artisan $(cmd)

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

fresh: ## Fresh migrate and seed
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Run database seeders
	docker compose exec app php artisan db:seed

test: ## Run tests
	docker compose exec app php artisan test

setup: ## Initial project setup
	docker compose up -d
	docker compose exec app ./setup.sh
	docker compose exec app cp env.development.example .env
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	docker compose exec app php artisan passport:install
	docker compose exec app php artisan telescope:install

queue: ## Start queue worker
	docker compose exec app php artisan queue:work

telescope: ## Publish Telescope assets
	docker compose exec app php artisan telescope:publish

clear: ## Clear all caches
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

install: ## Install dependencies
	docker compose exec app composer install

update: ## Update dependencies
	docker compose exec app composer update

# Development helpers
dev: up ## Start development environment
	@echo "Development environment started at http://localhost:8000"
	@echo "Telescope available at http://localhost:8000/telescope"

status: ## Show container status
	docker compose ps
