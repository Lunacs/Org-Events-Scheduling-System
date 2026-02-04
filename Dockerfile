# Stage 1: Build assets
FROM node:20-alpine AS node-build
WORKDIR /app

# Install PHP and Composer for Tailwind CSS to scan vendor files (MaryUI components)
RUN apk add --no-cache php83 php83-phar php83-json php83-mbstring php83-openssl php83-curl php83-dom php83-tokenizer php83-xml php83-xmlwriter && \
    ln -sf /usr/bin/php83 /usr/bin/php
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Install Composer dependencies first (needed for Tailwind @source directives)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --ignore-platform-reqs --no-autoloader

# Install npm dependencies
COPY package*.json ./
RUN npm ci

# Copy source files for asset building
COPY resources ./resources
COPY vite.config.js ./

# Build assets (vendor/robsontenorio/mary now exists for Tailwind to scan)
RUN npm run build

# Stage 2: PHP dependencies
FROM composer:2 AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize

# Stage 3: Production
FROM php:8.4-fpm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libwebp-dev libonig-dev default-mysql-client ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd fileinfo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy built assets and vendor from build stages
COPY --from=composer-build /app/vendor ./vendor
COPY --from=node-build /app/public/build ./public/build

# Copy application code
COPY . .

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Config files
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

ENV APP_ENV=production
ENV APP_DEBUG=true
ENV LOG_CHANNEL=stderr

EXPOSE 80
CMD ["/start.sh"]
