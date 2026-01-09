# ================================
# 1) BUILDER: Composer Dependencies
# ================================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# ================================
# 2) BUILDER: Node for Filament assets
# ================================
FROM node:20-alpine AS node
WORKDIR /app

COPY package*.json ./
RUN npm install --frozen-lockfile

COPY . .
RUN npm run build


# ================================
# 3) FINAL IMAGE: FrankenPHP Runtime
# ================================
FROM dunglas/frankenphp:1-php8.3 AS runner

# Set working directory
WORKDIR /app

# Install required PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    intl \
    sockets \
    opcache \
    bcmath \
    zip

# Copy Laravel app
COPY . .

# Copy vendor from composer stage
COPY --from=vendor /app/vendor ./vendor

# Copy built assets
COPY --from=node /app/public/build ./public/build

# Ensure cache & storage are writable
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Optimize Laravel
RUN php artisan optimize

# Env for production
ENV APP_ENV=production
ENV SERVE_STATIC_FILES=true
ENV FRANKENPHP_CONFIG="worker ./public/index.php 8"

# Expose FrankenPHP default port
EXPOSE 8080

CMD ["php", "frankenphp", "run", "--config=none"]
