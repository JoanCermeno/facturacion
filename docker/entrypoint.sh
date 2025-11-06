#!/bin/bash

echo "Esperando que la base de datos esté lista..."
sleep 5

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Ejecutando seeder..."
php artisan db:seed --force

echo "Iniciando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
