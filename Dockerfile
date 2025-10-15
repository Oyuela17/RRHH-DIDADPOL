# Base estable con Apache
FROM php:8.2-apache

# Habilitar rewrite y apuntar al /public de Laravel
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# apt sin interacción
ENV DEBIAN_FRONTEND=noninteractive

# Dependencias mínimas + extensiones PHP necesarias para Laravel + Postgres
RUN set -eux; \
    apt-get update -o Acquire::Retries=5; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
        zlib1g-dev; \
    update-ca-certificates; \
    docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql zip; \
    apt-get clean; rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código
WORKDIR /var/www/html
COPY . .

# Instalar dependencias Laravel y cachear
RUN set -eux; \
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
    php artisan key:generate --force || true; \
    mkdir -p storage bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache; \
    php artisan config:cache; \
    php artisan route:cache; \
    php artisan view:cache

EXPOSE 80
CMD ["apache2-foreground"]
