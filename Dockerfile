# ================================
# 1) BUILDER: Composer Dependencies
# ================================
FROM composer:2-php8.3 AS vendor

# Install runtime extensions needed for composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install intl gd zip

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist


# ================================
# 2) BUILDER: Node for Filament assets
# ================================
FROM node:20-alpine AS node
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build


# ================================
# 3) FINAL IMAGE: FrankenPHP Runtime
# ================================
FROM dunglas/frankenphp:1.0-php8.3 AS runner

# Install PHP extensions for Laravel + Filament
RUN install-php-extensions \
    pdo_mysql \
    intl \
    gd \
    mbstring \
    bcmath \
    opcache \
    zip \
    sockets

WORKDIR /app

# Copy application files
COPY . .

# Copy vendors from composer stage
COPY --from=vendor /app/vendor ./vendor

# Copy built assets
COPY --from=node /app/public/build ./public/build

# Ensure storage & cache writable
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production
ENV SERVE_STATIC_FILES=true
ENV FRANKENPHP_CONFIG="worker ./public/index.php 8"

EXPOSE 8080

CMD ["php", "frankenphp", "run", "--config=none"]
