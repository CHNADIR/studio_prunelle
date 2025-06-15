# Stage 1: Build Node.js assets
FROM node:18-alpine as node_builder

WORKDIR /app_node

COPY package.json ./
COPY package-lock.json ./ 
# Décommentez et assurez-vous que package-lock.json est versionné

RUN npm install

COPY webpack.config.js ./
COPY assets ./assets

RUN npm run build

# Stage 2: PHP Application
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Arguments pour l'utilisateur et le groupe (peuvent être passés à la construction)
ARG UID=1000
ARG GID=1000

# Installer les dépendances système et les extensions PHP
# Retrait de nginx et supervisor
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    mysql-client \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    gd \
    opcache \
    mbstring \
    xml \
    exif \
    bcmath \
    sockets

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Optimisation du cache Docker pour Composer
# Copier uniquement les fichiers nécessaires pour l'installation des dépendances
COPY composer.json composer.lock symfony.lock ./
# COPY .env ./ # Décommentez si vos scripts composer ont besoin de variables de .env (ex: APP_ENV)

# Ajustement des options de composer install pour le développement
# Retrait de --no-scripts et --no-dev
RUN composer install --prefer-dist --no-progress --no-interaction \
    && composer clear-cache

# Copier le reste de l'application
# .env.local et autres .env spécifiques ne sont pas copiés dans l'image
COPY . .

# Copier les assets construits depuis le stage node_builder
COPY --from=node_builder /app_node/public/build ./public/build

# Définir les permissions
RUN mkdir -p var/cache var/log var/uploads \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Exposer le port pour PHP-FPM
EXPOSE 9000

# Commande par défaut
CMD ["php-fpm"]