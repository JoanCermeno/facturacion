# -------------------------------
# STAGE 1 – PHP Dependencies (Builder)
# -------------------------------
FROM php:8.2-fpm AS php-builder

# Dependencias necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git zip unzip curl libzip-dev libicu-dev libxml2-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl mbstring

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------------
# STAGE 2 – PHP-FPM + NGINX
# -------------------------------
FROM php:8.2-fpm

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx

# Copiar código desde el builder
COPY --from=php-builder /var/www/html /var/www/html

WORKDIR /var/www/html

# Copiar configuración de Nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Copiar y preparar entrypoint
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permisos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
