# Base estable (Debian Bookworm) con Apache
FROM php:8.3-apache

# Apache listo para Laravel (public/ como DocumentRoot)
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# No pedir interacción en apt
ENV DEBIAN_FRONTEND=noninteractive

# ---------- Bloque de dependencias con diagnóstico ----------
# - --no-install-recommends para no jalar paquetes extra
# - reintentos en apt-get update
# - si falla, mostramos /etc/apt/sources.list* y el log
RUN set -eux; \
    echo ">>> MOSTRANDO /etc/apt/sources.list* antes de update"; \
    ls -l /etc/apt/; \
    cat /etc/apt/sources.list || true; \
    for f in /etc/apt/sources.list.d/*.list; do echo ">>> $f"; cat "$f" || true; done || true; \
    echo ">>> apt-get update (con reintentos)"; \
    (apt-get update -o Acquire::Retries=5) || (echo ">>> apt update FALLÓ; mostrando sources otra vez" && cat /etc/apt/sources.list && for f in /etc/apt/sources.list.d/*.list; do echo ">>> $f"; cat "$f" || true; done && exit 1); \
    echo ">>> apt-get install dependencias PHP y de compilación"; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev; \
    update-ca-certificates; \
    echo ">>> configurar y compilar extensiones PHP (gd, zip, pdo_pgsql, etc)"; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Composer (desde imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código
WORKDIR /var/www/html
COPY . .

# Dependencias de Laravel y caches
RUN set -eux; \
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
    php artisan key:generate --force || true; \
    mkdir -p storage bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache; \
    php artisan config:cache; \
    php artisan route:cache; \
    php artisan view:cache

# Limpieza de apt (después de todo)
RUN set -eux; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

EXPOSE 80
CMD ["apache2-foreground"]
