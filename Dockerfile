# -------------------------------
# STAGE 1 – Builder con Debian
# -------------------------------
FROM debian:bullseye AS php-builder

# Instalar PHP y extensiones necesarias
RUN apt-get update && apt-get install -y \
    php php-fpm php-mysql php-zip php-intl php-mbstring php-curl php-xml php-bcmath php-cli php-common php-gd php-readline \
    git zip unzip curl libzip-dev libicu-dev libonig-dev libxml2-dev libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    nginx

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --optimize-autoloader --no-interaction --no-dev

# -------------------------------
# STAGE 2 – Producción con Nginx
# -------------------------------
FROM debian:bullseye

# Instalar PHP y Nginx en producción
RUN apt-get update && apt-get install -y \
    php php-fpm php-mysql php-zip php-intl php-mbstring php-curl php-xml php-bcmath php-cli php-common php-gd php-readline \
    nginx

WORKDIR /var/www/html

# Copiar código desde el builder
COPY --from=php-builder /var/www/html /var/www/html

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