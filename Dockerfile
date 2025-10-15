# Base estable con Apache
FROM php:8.2-apache

# Habilitar rewrite y apuntar al /public de Laravel
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# apt sin interacción
ENV DEBIAN_FRONTEND=noninteractive

# Dependencias del sistema (incluye toolchain de compilación)
RUN set -eux; \
    apt-get update -o Acquire::Retries=5; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
        zlib1g-dev \
        build-essential \
        autoconf \
        pkg-config \
        libonig-dev \
    ; \
    update-ca-certificates; \
    rm -rf /var/lib/apt/lists/*

# --- Extensiones PHP (divididas para ver errores claros) ---
# ZIP / PDO / Postgres
RUN set -eux; \
    echo ">>> Install PHP ext: pdo, pdo_pgsql, zip"; \
    docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql zip

# MBSTRING
RUN set -eux; \
    echo ">>> Install PHP ext: mbstring"; \
    docker-php-ext-install -j"$(nproc)" mbstring

# BCMATH
RUN set -eux; \
    echo ">>> Install PHP ext: bcmath"; \
    docker-php-ext-install -j"$(nproc)" bcmath

# EXIF
RUN set -eux; \
    echo ">>> Install PHP ext: exif"; \
    docker-php-ext-install -j"$(nproc)" exif

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Código
WORKDIR /var/www/html
COPY . .

# Dependencias de Laravel (sin artisan aún)
RUN set -eux; \
    install -d -m 0775 storage bootstrap/cache; \
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Comando de inicio
CMD set -eux; \
    php artisan config:clear || true; \
    php artisan cache:clear || true; \
    php artisan route:clear || true; \
    php artisan view:clear || true; \
    php artisan migrate --force || true; \
    exec apache2-foreground
