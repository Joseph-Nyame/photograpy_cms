#!/bin/bash
set -e

echo "=== Starting Lumen Studio ==="

if [ ! -d "vendor" ]; then
    echo "vendor/ not found, running composer install..."
    composer install --no-interaction --optimize-autoloader
fi

echo "Ensuring directory structure..."
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

if [ -L public/storage ] && [ ! -e public/storage ]; then
    echo "Fixing broken storage symlink..."
    rm public/storage
fi

if [ ! -L public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link --force 2>/dev/null || true
fi

echo "Waiting for database..."
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_USER=${DB_USERNAME}
DB_PASS=${DB_PASSWORD}
DB_NAME=${DB_DATABASE}

DB_READY=false
for i in $(seq 1 60); do
    if php -r "
        try {
            new PDO('mysql:host=$DB_HOST;dbname=$DB_NAME;port=$DB_PORT', '$DB_USER', '$DB_PASS');
            exit(0);
        } catch (PDOException \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        echo "Database ready!"
        DB_READY=true
        break
    fi
    echo "Attempt $i/60: Database not ready, sleeping..."
    sleep 2
done

if [ "$DB_READY" != "true" ]; then
    echo "Database did not become ready in time."
    exit 1
fi

echo "Running migrations..."
MIGRATED=false
for i in $(seq 1 10); do
    if php artisan migrate --force; then
        MIGRATED=true
        break
    fi
    echo "Migration attempt $i/10 failed, retrying in 5 seconds..."
    sleep 5
done

if [ "$MIGRATED" != "true" ]; then
    echo "Migrations failed."
    exit 1
fi

if [ "${SEED_DATABASE:-true}" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Discovering packages..."
php artisan package:discover --ansi

echo "=== Application startup complete ==="

exec docker-php-serversideup-entrypoint /init
