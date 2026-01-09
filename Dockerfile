##############################################
# FRANKENPHP
##############################################
FROM dunglas/frankenphp:1-php8.3 AS runtime

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libicu-dev libpng-dev libjpeg-dev libfreetype-dev libzip-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl gd zip pcntl bcmath opcache mysqli

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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

#install composer
RUN composer install --no-dev --no-interaction --prefer-dist

##############################################
# NODE STAGE FOR FRONTEND ASSETS
##############################################
FROM node:20-alpine AS node_build

WORKDIR /app
COPY package.json package-lock.json* yarn.lock* ./
RUN npm install

COPY . .
RUN npm run build

# Laravel optimization
RUN php artisan optimize
RUN php artisan storage:link

# FrankenPHP runtime env
ENV APP_ENV=production
ENV SERVE_STATIC_FILES=true

CMD ["frankenphp", "run", "--config=/app/Caddyfile"]
