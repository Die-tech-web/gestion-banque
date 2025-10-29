.PHONY: help build up down restart logs shell migrate seed test

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build the Docker images
	docker-compose build

up: ## Start the containers
	docker-compose up -d

down: ## Stop the containers
	docker-compose down

restart: ## Restart the containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access the app container shell
	docker-compose exec app sh

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate --force

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed --force

fresh: ## Refresh database with seeders
	docker-compose exec app php artisan migrate:fresh --seed --force

test: ## Run tests
	docker-compose exec app php artisan test

key: ## Generate application key
	docker-compose exec app php artisan key:generate

cache: ## Clear and cache config
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

clean: ## Clean up Docker resources
	docker-compose down -v --remove-orphans
	docker system prune -f