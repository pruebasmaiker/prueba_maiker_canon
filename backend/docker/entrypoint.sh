#!/bin/bash

# Esperar a que la base de datos esté lista
echo "Waiting for database connection..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "Database is ready!"

# Generar clave de aplicación si no existe
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Configurar permisos
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Ejecutar migraciones
echo "Running migrations..."
php artisan migrate --force

# Ejecutar seeders
echo "Running seeders..."
php artisan db:seed --force

# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Iniciar Apache
apache2-foreground 