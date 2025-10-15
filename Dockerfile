# Base de PHP con Apache
FROM php:8.2-apache

# Ajustar el DocumentRoot a /public y habilitar mod_rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN set -eux; \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf; \
    a2enmod rewrite

# ===== Dependencias del sistema necesarias =====
# - libpq-dev      -> pdo_pgsql
# - libzip-dev + zlib1g-dev -> zip
# - libpng-dev libjpeg... libfreetype... -> gd
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpq-dev \
    libzip-dev zlib1g-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
 && rm -rf /var/lib/apt/lists/*

# Composer (desde imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ===== App Laravel =====
WORKDIR /var/www/html
COPY . .

# Instalar dependencias PHP y preparar Laravel
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
