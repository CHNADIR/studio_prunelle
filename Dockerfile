FROM composer:latest AS composer

WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

FROM node:20-alpine AS node_builder

WORKDIR /app_node
COPY --from=composer /app/vendor/ ./vendor/
COPY package.json package-lock.json ./
RUN npm install --no-package-lock @symfony/ux-turbo@^2.26.0 && \
    npm install --force
COPY webpack.config.js ./
COPY assets ./assets

RUN NODE_ENV=production npm run build || echo "Build warnings detected but continuing..."

FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

ARG UID=1000
ARG GID=1000

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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log /var/www/html/var/uploads \
    /var/www/html/public/uploads /var/www/html/public/build

COPY --from=composer /app/vendor/ ./vendor/

COPY --from=node_builder /app_node/public/build/ ./public/build/

COPY . .

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 9000

RUN mkdir -p /var/www/html/src/Security/Voter && \
    chmod -R 755 /var/www/html/src/Security

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]