# -------------------------------
# STAGE 1 – PHP Dependencies
# -------------------------------
FROM php:8.2-fpm AS php-builder

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear directorio
WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# -------------------------------
# STAGE 2 – Imagen final con PHP + NGINX
# -------------------------------
FROM php:8.2-fpm

# Instalar Nginx y utilidades
RUN apt-get update && apt-get install -y nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql

# Copiar código desde el builder
COPY --from=php-builder /var/www/html /var/www/html

WORKDIR /var/www/html

# Copiar configuración de Nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Copiar configuración de Supervisor
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar script de arranque
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
