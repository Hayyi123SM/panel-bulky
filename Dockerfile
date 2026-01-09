FROM dunglas/frankenphp:1.2.5-php8.3
WORKDIR /app

# Install system deps
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpq-dev \
    nodejs npm curl && \
    docker-php-ext-install intl

# Copy project
COPY . .

# Install composer deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend
RUN npm ci && npm run build

# Optimize Laravel
RUN php artisan storage:link || true && \
    php artisan optimize

EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]
