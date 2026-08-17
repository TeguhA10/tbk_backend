#!/bin/bash
set -e

# Copy .env if not exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Ensure Docker environment variables are synced into .env
if [ -f .env ] && [ -n "$DB_HOST" ]; then
    sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env 2>/dev/null || true
    sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT:-5432}/" .env 2>/dev/null || true
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-db_tbk}/" .env 2>/dev/null || true
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-postgres}/" .env 2>/dev/null || true
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-password123!}/" .env 2>/dev/null || true
fi

# Ensure storage and cache directories exist and have proper permissions
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

# Wait for PostgreSQL
echo "Waiting for PostgreSQL database at $DB_HOST:$DB_PORT..."
until php -r "
try {
    new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"; do
    sleep 2
done
echo "PostgreSQL is up and ready!"

# Generate app key if needed
if ! grep -q "APP_KEY=base64:" .env && [ -z "$APP_KEY" ]; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Run migrations and seed data
echo "Running database migrations..."
php artisan migrate --force

echo "Checking if database seeding is needed..."
php artisan db:seed --force || true

echo "Starting Laravel Artisan Server on 0.0.0.0:8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
