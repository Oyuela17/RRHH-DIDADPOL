# Imagen base oficial de PHP con Apache
FROM php:8.3-apache

# Habilitar mod_rewrite y establecer el DocumentRoot
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev zlib1g-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar el proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && php artisan key:generate --force || true \
 && mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 80
CMD ["apache2-foreground"]
