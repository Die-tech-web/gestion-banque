#!/bin/bash
set -e

# Wait for database to be ready
until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
    echo "Waiting for database..."
    sleep 2
done

# Run migrations
php artisan migrate --force

# Run seeders if needed
# php artisan db:seed --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger documentation
php artisan l5-swagger:generate

# Start Apache
apache2-foreground