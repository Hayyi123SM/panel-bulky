##############################################
# 1) COMPOSER BUILD STAGE
##############################################
FROM composer:2 AS composer_build

WORKDIR /app

COPY . .

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
# 3) FRANKENPHP RUNTIME
##############################################
FROM dunglas/frankenphp:1-php8.3 AS runtime

WORKDIR /app

# Install PHP runtime extensions
RUN install-php-extensions \
    pdo_mysql \
    intl \
    gd \
    mbstring \
    bcmath \
    opcache \
    zip \
    sockets

# Copy application source
COPY . .

# Copy vendor from composer stage
COPY --from=composer_build /app/vendor ./vendor

# Copy build assets from node stage
COPY --from=node_build /app/public/build ./public/build

# Laravel optimization
RUN php artisan optimize --no-interaction || true
RUN php artisan storage:link || true

# Runtime env
ENV APP_ENV=production
ENV SERVE_STATIC_FILES=true

# Run FrankenPHP with Caddyfile
CMD ["frankenphp", "run", "--config=/app/Caddyfile"]
