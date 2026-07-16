FROM php:8.5-fpm-alpine AS base

RUN apk add --no-cache \
    bash git curl unzip libzip-dev libpng-dev libxml2-dev oniguruma-dev icu-dev tzdata \
    && docker-php-ext-install \
       pdo_mysql zip gd intl bcmath \
    && rm -rf /var/cache/apk/*

ENV TZ=Europe/Warsaw
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM node:24-alpine AS frontend
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources/ ./resources/
RUN npm ci && npm run build

FROM base AS app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
COPY --from=frontend /app/public/build /var/www/html/public/build
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link || true

# Health check endpoint /up exposed by Laravel
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php -r "echo file_get_contents('http://127.0.0.1:9000/up') ? 0 : 1;" || exit 1

EXPOSE 9000
CMD ["php-fpm"]
