# ---------- Base PHP + Apache ----------
FROM php:8.2-apache

# Apache: document root => /public y rewrite
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Evitar prompts en apt
ENV DEBIAN_FRONTEND=noninteractive

# ---------- Paquetes del sistema ----------
# (build-essential, autoconf, pkg-config para compilar; libs para zip, pgsql y gd)
RUN set -eux; \
  apt-get update -o Acquire::Retries=5; \
  apt-get install -y --no-install-recommends \
    ca-certificates git curl unzip \
    build-essential autoconf pkg-config \
    libpq-dev libzip-dev zlib1g-dev \
    libjpeg62-turbo-dev libpng-dev libfreetype6-dev \
    libonig-dev; \
  update-ca-certificates; \
  rm -rf /var/lib/apt/lists/*

# ---------- Extensiones PHP ----------
# zip + pdo + pgsql
RUN set -eux; echo ">>> PHP ext: pdo, pdo_pgsql, zip"; \
  docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql zip

# mbstring
RUN set -eux; echo ">>> PHP ext: mbstring"; \
  docker-php-ext-install -j"$(nproc)" mbstring

# bcmath
RUN set -eux; echo ">>> PHP ext: bcmath"; \
  docker-php-ext-install -j"$(nproc)" bcmath

# exif
RUN set -eux; echo ">>> PHP ext: exif"; \
  docker-php-ext-install -j"$(nproc)" exif

# gd (requiere las libs de jpeg/png/freetype ya instaladas)
RUN set -eux; echo ">>> PHP ext: gd"; \
  docker-php-ext-configure gd --with-jpeg --with-freetype; \
  docker-php-ext-install -j"$(nproc)" gd

# OPcache para prod
RUN set -eux; echo ">>> PHP ext: opcache"; \
  docker-php-ext-install opcache; \
  { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
  } > /usr/local/etc/php/conf.d/opcache.ini

# ---------- Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

# ---------- App ----------
WORKDIR /var/www/html

# 1) Instala vendor en una capa caché (rápido en redeploys)
COPY composer.json composer.lock ./
RUN set -eux; \
  install -d -m 0775 storage bootstrap/cache storage/logs; \
  composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# 2) Copia el resto del proyecto
COPY . .

# 3) Permisos para runtime de Laravel (+ log file y symlink de storage)
RUN set -eux; \
  mkdir -p storage/logs bootstrap/cache; \
  touch storage/logs/laravel.log; \
  chown -R www-data:www-data storage bootstrap/cache; \
  find storage bootstrap/cache -type d -exec chmod 775 {} \; ; \
  find storage bootstrap/cache -type f -exec chmod 664 {} \; ; \
  php artisan storage:link || true

# ---------- Arranque ----------
# Limpia/optimiza y migra si hay DB; no rompe si falla
CMD set -eux; \
  php artisan config:clear   || true; \
  php artisan cache:clear    || true; \
  php artisan route:clear    || true; \
  php artisan view:clear     || true; \
  php artisan key:generate --force || true; \
  php artisan migrate --force      || true; \
  exec apache2-foreground
