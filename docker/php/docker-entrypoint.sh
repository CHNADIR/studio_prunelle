#!/bin/sh
set -e

# Vérifier si composer est installé et disponible
if command -v composer > /dev/null 2>&1; then
    # Vérifier si les packages nécessaires sont installés
    if ! grep -q "\"symfony/rate-limiter\"" /var/www/html/composer.json; then
        echo "Rate-limiter package not found in composer.json, adding it..."
        composer require symfony/rate-limiter:^6.4 --no-interaction
    fi
    
    if ! grep -q "\"symfony/lock\"" /var/www/html/composer.json; then
        echo "Lock package not found in composer.json, adding it..."
        composer require symfony/lock:^6.4 --no-interaction
    fi
fi

# Si le fichier autoload_runtime.php n'existe pas, installer les dépendances
if [ ! -f /var/www/html/vendor/autoload_runtime.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Le reste du script reste inchangé...
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