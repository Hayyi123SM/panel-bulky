##############################################
# 1) COMPOSER BUILD STAGE (PHP 8.3)
##############################################
FROM php:8.3-cli AS composer_build

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpng-dev libjpeg-dev libfreetype-dev libzip-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl gd zip pcntl bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# install backend deps
RUN composer install --no-dev --no-interaction --prefer-dist

##############################################
# 2) NODE BUILD STAGE
##############################################
FROM node:20-alpine AS node_build

WORKDIR /app
COPY package.json package-lock.json* yarn.lock* ./
RUN npm install

COPY . .
RUN npm run build


##############################################
# 3) FRANKENPHP RUNTIME STAGE
##############################################
FROM dunglas/frankenphp:1-php8.3 AS runtime

WORKDIR /app

# install php extensions untuk octane
RUN install-php-extensions \
    pdo_mysql \
    intl \
    gd \
    mbstring \
    bcmath \
    opcache \
    zip \
    sockets \
    pcntl \
    posix

# copy app & built assets
COPY . .
COPY --from=composer_build /app/vendor ./vendor
COPY --from=node_build /app/public/build ./public/build

COPY .env.example .env
RUN php artisan key:generate

RUN php artisan octane:install --server=frankenphp

# laravel caches (opsional)
RUN php artisan optimize --no-interaction || true
RUN php artisan storage:link || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# CMD ["frankenphp", "php-server"]
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=2"]

