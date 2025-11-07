# -------------------------------
# STAGE 1 – PHP Dependencies
# -------------------------------
FROM php:8.2-fpm AS php-builder

# Dependencias del sistema necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpq-dev libzip-dev libonig-dev libxml2-dev \
    libicu-dev libssl-dev pkg-config \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Fileinfo y Mbstring vienen integradas, pero aseguramos la compilación:
RUN docker-php-ext-install mbstring

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear directorio
WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------------
# STAGE 2 – Imagen final con PHP + NGINX
# -------------------------------
FROM php:8.2-fpm

# Instalar Nginx y Supervisor
RUN apt-get update && apt-get install -y nginx supervisor \
    libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl mbstring

WORKDIR /var/www/html

# Copiar código desde builder
COPY --from=php-builder /var/www/html /var/www/html

# Copiar configuración de Nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Copiar configuración de Supervisor
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar entrypoint
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
