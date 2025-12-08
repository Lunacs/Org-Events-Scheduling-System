# Stage 1: Build assets
FROM node:20-alpine AS node-build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# Stage 2: PHP dependencies
FROM composer:2 AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize

# Stage 3: Production
FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    default-mysql-client ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd \
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
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

EXPOSE 80
CMD ["/start.sh"]
