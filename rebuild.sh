#!/bin/bash
set -e  # Arrêter le script si une commande échoue

echo "Arrêt des conteneurs..."
docker compose down

echo "Suppression des volumes problématiques..."
docker volume rm prunelle_var 2>/dev/null || true

echo "Vérification du composer.json..."
if ! grep -q "\"symfony/rate-limiter\"" composer.json; then
    echo "Ajout manuel de symfony/rate-limiter au composer.json..."
    sed -i '/symfony\/runtime/i \ \ \ \ \ \ \ \ "symfony\/rate-limiter": "^6.4",' composer.json
fi

# Vérifier également si symfony/lock est présent
if ! grep -q "\"symfony/lock\"" composer.json; then
    echo "Ajout manuel de symfony/lock au composer.json..."
    sed -i '/symfony\/rate-limiter/a \ \ \ \ \ \ \ \ "symfony\/lock": "^6.4",' composer.json
fi

echo "Reconstruction des images..."
docker compose build --no-cache

echo "Démarrage des conteneurs..."
docker compose up -d

echo "Installation des dépendances Composer..."
docker compose exec app composer install --no-interaction

echo "Vidage du cache Symfony..."
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console cache:warmup

echo "Installation des dépendances NPM et build des assets..."
docker compose exec node npm install --force
docker compose exec node npm run build

echo "Vérification de la structure des répertoires..."
docker compose exec app mkdir -p /var/www/html/src/Security/Voter
docker compose exec app ls -la /var/www/html/src/Security/Voter

echo "Reconstruction terminée. Accédez à http://localhost:8000"