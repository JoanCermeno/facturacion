# -------------------------------
# STAGE 1 – Builder (PHP 8.3 FPM)
# -------------------------------
# Usamos la imagen oficial de PHP con FPM, CLI y basada en Debian Bullseye (estable)
FROM php:8.3-fpm-bullseye AS php-builder

# Instalar dependencias del sistema necesarias
# ¡Mucho más simple! Las extensiones de PHP se instalan con 'docker-php-ext-install'
RUN apt-get update && apt-get install -y \
    git zip unzip curl libzip-dev libicu-dev libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar y configurar extensiones de PHP
# Aquí agregamos las que solicitaste más algunas comunes
RUN docker-php-ext-install pdo_mysql intl mbstring curl xml bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
# Usa la versión correcta de PHP (8.3) para instalar las dependencias
RUN composer install --optimize-autoloader --no-interaction --no-dev

# -------------------------------
# STAGE 2 – Producción (PHP 8.3 + Nginx)
# -------------------------------
# Imagen base final: PHP-FPM 8.3 para ejecutar la aplicación
FROM php:8.3-fpm-bullseye

# Instalar Nginx y sus dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    # Limpiar caché para reducir el tamaño de la imagen final
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar código y dependencias desde el builder
# Solo copiamos los archivos esenciales para una imagen más ligera
COPY --from=php-builder /var/www/html /var/www/html

# Copiar configuración de Nginx y Entrypoint
# Asume que estos archivos están en una carpeta 'docker/' en la raíz de tu proyecto.
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permisos para Laravel (www-data es el usuario por defecto de PHP-FPM)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]