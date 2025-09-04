FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 777 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# Run artisan commands on container start
CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan serve --host=0.0.0.0 --port=8000
