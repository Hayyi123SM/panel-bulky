##############################################
# 1) BASE PHP FOR COMPOSER (BUILD STAGE)
##############################################
FROM php:8.3-cli AS composer_build

# Install system deps for intl, gd, zip, etc
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP extensions
RUN docker-php-ext-configure intl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions needed by Laravel + Filament
RUN docker-php-ext-install intl gd zip pcntl bcmath opcache mysqli

# Copy Composer binary from official image (clean & recommended)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir and install dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist

##############################################
# 2) NODE STAGE FOR FRONTEND ASSETS
##############################################
FROM node:20-alpine AS node_build

WORKDIR /app
COPY package.json package-lock.json* yarn.lock* ./
RUN npm install

COPY . .
RUN npm run build

##############################################
# 3) RUNTIME: FRANKENPHP
##############################################
FROM dunglas/frankenphp:1-php8.3 AS runtime

WORKDIR /app

# Install runtime PHP extensions (lighter than build)
RUN install-php-extensions \
    pdo_mysql \
    intl \
    gd \
    mbstring \
    bcmath \
    opcache \
    zip \
    sockets

# Copy app source
COPY . .

# Copy vendor from composer build
COPY --from=composer_build /app/vendor ./vendor

# Copy build assets
COPY --from=node_build /app/public/build ./public/build

# Permissions for storage
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

# Laravel optimization
RUN php artisan optimize

# FrankenPHP runtime env
ENV APP_ENV=production
ENV SERVE_STATIC_FILES=true
ENV FRANKENPHP_CONFIG="worker ./public/index.php 4"

EXPOSE 8080

CMD ["php", "frankenphp", "run", "--config=none"]
