# GaragePro — Makefile

.PHONY: help install up down build test migrate migrate-diff fixtures lint cs-fix cache-clear backup shell logs

help: ## Affiche l'aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Installation initiale
	cp .env.docker .env
	docker compose build --no-cache
	docker compose up -d
	sleep 5
	$(MAKE) migrate
	$(MAKE) fixtures

up: ## Démarre les conteneurs
	docker compose up -d

down: ## Arrête les conteneurs
	docker compose down

build: ## Rebuild les images
	docker compose build --no-cache

migrate: ## Exécute les migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff: ## Génère une migration
	docker compose exec php php bin/console doctrine:migrations:diff

fixtures: ## Charge les fixtures
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction || true

shell: ## Ouvre un shell dans le conteneur PHP
	docker compose exec php sh

logs: ## Affiche les logs
	docker compose logs -f

test: ## Lance les tests
	docker compose exec php php bin/console doctrine:database:create --if-not-exists --env=test
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --env=test
	docker compose exec php vendor/bin/phpunit

lint: ## Vérifie la qualité du code
	docker compose exec php php bin/console lint:twig templates
	docker compose exec php php bin/console lint:yaml config
	docker compose exec php php bin/console lint:container
	docker compose exec php vendor/bin/phpstan analyse src --level=6 || true

cs-fix: ## Corrige le style du code
	docker compose exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php || true

cache-clear: ## Vide le cache
	docker compose exec php php bin/console cache:clear

backup: ## Sauvegarde la base de données
	docker compose exec database mysqldump -u garage_user -pgarage_pass garage_pro > backup_$$(date +%Y%m%d_%H%M%S).sql
