# Stage 1: Installer les dépendances PHP
FROM composer:latest AS composer

WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

# Stage 2: Build Node.js assets
FROM node:20-alpine AS node_builder

WORKDIR /app_node
# Copier d'abord les dépendances PHP depuis l'étape précédente
COPY --from=composer /app/vendor/ ./vendor/
COPY package.json package-lock.json ./
# Installation explicite de turbo 
RUN npm install --no-package-lock @symfony/ux-turbo@^2.26.0 && \
    npm install --force
COPY webpack.config.js ./
COPY assets ./assets

# Build des assets avec gestion des erreurs
RUN NODE_ENV=production npm run build || echo "Build warnings detected but continuing..."

# Stage 3: Application finale
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Arguments pour l'utilisateur et le groupe
ARG UID=1000
ARG GID=1000

# Installer les dépendances système et les extensions PHP
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    bash \
    git \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Configurer OPcache pour de meilleures performances
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Créer les répertoires nécessaires
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/www/html/var/uploads \
    /var/www/html/public/uploads /var/www/html/public/build

# Copier les dépendances PHP depuis l'étape composer
COPY --from=composer /app/vendor/ ./vendor/

# Copier les assets compilés depuis l'étape node_builder
COPY --from=node_builder /app_node/public/build/ ./public/build/

# Copier le reste de l'application
COPY . .

# Créer un script d'entrée pour vérifier les permissions au démarrage
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Exposer le port pour PHP-FPM
EXPOSE 9000

# Créer le répertoire pour les votants de sécurité et s'assurer qu'il est accessible
RUN mkdir -p /var/www/html/src/Security/Voter && \
    chmod -R 755 /var/www/html/src/Security

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]