#!/bin/sh
set -e

# Créer les répertoires nécessaires s'ils n'existent pas
mkdir -p /var/www/html/var/cache
mkdir -p /var/www/html/var/log
mkdir -p /var/www/html/var/uploads
mkdir -p /var/www/html/public/uploads

# Assurer les bonnes permissions
chmod -R 777 /var/www/html/var/cache
chmod -R 777 /var/www/html/var/log
chmod -R 777 /var/www/html/var/uploads
chmod -R 777 /var/www/html/public/uploads

# Exécuter la commande fournie (généralement php-fpm)
exec "$@"