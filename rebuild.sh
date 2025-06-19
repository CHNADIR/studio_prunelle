echo "Arrêt des conteneurs..."
docker compose down

echo "Suppression des volumes problématiques..."
docker volume rm prunelle_var 2>/dev/null || true

echo "Reconstruction des images..."
docker compose build --no-cache

echo "Démarrage des conteneurs..."
docker compose up -d

echo "Installation des dépendances Composer..."
docker compose exec app composer install

echo "Installation des dépendances NPM et build des assets..."
docker compose exec node npm install --force
docker compose exec node npm run build

echo "Vérification de la structure des répertoires..."
docker compose exec app mkdir -p /var/www/html/src/Security/Voter
docker compose exec app ls -la /var/www/html/src/Security/Voter

echo "Vidage du cache Symfony..."
docker compose exec app php bin/console cache:clear

echo "Reconstruction terminée. Accédez à http://localhost:8000"