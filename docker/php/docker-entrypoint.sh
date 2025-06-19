#!/bin/sh
set -e

# Si le fichier autoload_runtime.php n'existe pas, installer les dépendances
if [ ! -f /var/www/html/vendor/autoload_runtime.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Créer les répertoires nécessaires avec les bonnes permissions
mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/www/html/var/uploads
mkdir -p /var/www/html/public/uploads /var/www/html/public/build
mkdir -p /var/www/html/src/Security/Voter  # Assurer que le répertoire des Voters existe

# Copier les Voters existants si nécessaire (au cas où les volumes montés les masquent)
if [ -f /var/www/html/src/Security/Voter/PriseDeVueVoter.php.dist ]; then
    cp /var/www/html/src/Security/Voter/PriseDeVueVoter.php.dist /var/www/html/src/Security/Voter/PriseDeVueVoter.php
fi

if [ -f /var/www/html/src/Security/Voter/EcoleVoter.php.dist ]; then
    cp /var/www/html/src/Security/Voter/EcoleVoter.php.dist /var/www/html/src/Security/Voter/EcoleVoter.php
fi

# Fixer les permissions de manière sécurisée et définitive
find /var/www/html/var -type d -exec chmod 777 {} \;
find /var/www/html/public/uploads -type d -exec chmod 777 {} \;
chmod -R 755 /var/www/html/src/Security

# Exécuter la commande fournie
exec "$@"