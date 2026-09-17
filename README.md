# GaragePro — Plateforme de maintenance automobile

Symfony 6.4 · PHP 8.2 · Bootstrap 5 · Doctrine ORM · Docker · CI/CD

## Architecture

- **16 entités Doctrine** (User, Customer, Vehicle, Intervention, Part, Invoice, Quote, Appointment, MaintenanceAlert, StockMovement, Supplier, PurchaseOrder...)
- **10 contrôleurs** avec authentification et voters
- **6 formulaires Symfony**
- **3 services métier** (Alertes, Workflow intervention, Gestion stock)
- **Templates Twig** responsive avec thème sombre
- **Panel Admin** complet (stats, utilisateurs, paramètres)
- **Docker** + **CI/CD GitHub Actions**

## 🐳 Démarrage rapide avec Docker

### Prérequis
- Docker + Docker Compose
- Make (optionnel)

### Installation

```bash
# 1. Cloner le projet
git clone <repo>
cd garage-pro

# 2. Lancer l'installation
make install

# Ou manuellement
docker-compose build --no-cache
docker-compose up -d
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### Accès

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| Mailpit (emails) | http://localhost:8025 |
| phpMyAdmin | http://localhost:8081 |
| MySQL | localhost:3306 |

### Commandes Make

```bash
make up          # Démarre les conteneurs
make down        # Arrête les conteneurs
make migrate     # Exécute les migrations
make fixtures    # Charge les fixtures
make test        # Lance les tests
make lint        # Vérifie la qualité du code
make cs-fix      # Corrige le style du code
make shell       # Shell dans le conteneur PHP
make logs        # Affiche les logs
make backup      # Sauvegarde la BDD
```

## 🔧 Installation classique (sans Docker)

```bash
composer install
# Configurer .env (DATABASE_URL)
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
symfony server:start
```

## 🧪 Tests

```bash
# Tests unitaires
vendor/bin/phpunit

# Tests avec couverture
vendor/bin/phpunit --coverage-html var/coverage

# Qualité du code
vendor/bin/phpstan analyse src --level=6
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## 🚀 CI/CD

### Workflows GitHub Actions

| Workflow | Déclencheur | Description |
|----------|-------------|-------------|
| `ci.yml` | Push/PR sur main/develop | Tests PHPUnit, PHPStan, lint Twig/YAML, sécurité |
| `cd-docker.yml` | Tag `v*.*.*` | Build + push image Docker sur GHCR + SBOM |
| `cd-deploy.yml` | Après build Docker | Déploiement SSH sur VPS + health check + Slack |

### Variables GitHub Secrets requises

- `SSH_HOST` — IP du serveur de production
- `SSH_USER` — Utilisateur SSH
- `SSH_PRIVATE_KEY` — Clé privée SSH
- `APP_DOMAIN` — Domaine de l'application
- `SLACK_WEBHOOK_URL` — Webhook Slack (optionnel)

### Déploiement production

```bash
# Taguer une version
git tag v1.0.0
git push origin v1.0.0

# Le CI/CD build et déploie automatiquement
```

## 🏗️ Structure du projet

```
garage-pro/
├── .github/workflows/      # CI/CD GitHub Actions
├── docker/                 # Config Docker (nginx, php, mysql)
├── src/
│   ├── Controller/         # Contrôleurs (10)
│   ├── Entity/             # Entités (16)
│   ├── Form/               # Formulaires (6)
│   ├── Repository/         # Repositories (16)
│   ├── Security/Voter/     # Voters
│   └── Service/            # Services métier (3)
├── templates/              # Templates Twig (30+)
├── tests/                  # Tests unitaires + fonctionnels
├── composer.json
├── docker-compose.yml
├── docker-compose.prod.yml
├── Dockerfile
└── Makefile
```

## 👤 Accès par défaut

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@garagepro.fr | admin123 |
| Mécanicien | mecano@garagepro.fr | mecano123 |

## 📋 Domaines fonctionnels

1. Auth & Sécurité (JWT-ready, 2FA)
2. Gestion Clients
3. Gestion Véhicules
4. Interventions (workflow complet)
5. Planning & Rendez-vous
6. Stock & Pièces détachées
7. Facturation & Paiements
8. Fournisseurs & Commandes
9. Alertes maintenance automatiques
10. Panel Admin (stats, utilisateurs, paramètres)
