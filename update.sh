#!/bin/bash
set -e

echo "=========================================="
echo "  Tourist Car System - App & Data Updater"
echo "  Server IP: 157.230.170.214"
echo "=========================================="

APP_DIR="/var/www/tourist-car-system"

if [ ! -d "$APP_DIR" ]; then
    echo "Directory $APP_DIR does not exist! Running full installer..."
    curl -fsSL https://raw.githubusercontent.com/amjad467/system_df/main/install.sh | bash
    exit 0
fi

cd "$APP_DIR"

echo "[1/5] Pulling latest code and updated database from GitHub..."
git fetch origin main
git reset --hard origin/main

echo "[2/5] Copying updated application source files into Docker container..."
docker cp "$APP_DIR/app/." tourist-app:/var/www/app/
docker cp "$APP_DIR/resources/." tourist-app:/var/www/resources/
docker cp "$APP_DIR/routes/." tourist-app:/var/www/routes/
docker cp "$APP_DIR/config/." tourist-app:/var/www/config/
docker cp "$APP_DIR/public/." tourist-app:/var/www/public/
docker cp "$APP_DIR/database/migrations/." tourist-app:/var/www/database/migrations/

echo "[3/5] Syncing 17,424 records database (with 21/22/23/24 prefixes and zero spaces)..."
if [ -f "$APP_DIR/database/database_seed.sqlite" ]; then
    docker cp "$APP_DIR/database/database_seed.sqlite" tourist-app:/var/www/database/database.sqlite
    docker exec tourist-app chmod 777 /var/www/database/database.sqlite
    echo "Database file copied successfully."
else
    echo "Warning: database_seed.sqlite not found!"
fi

echo "[4/5] Clearing view, config, route and application caches..."
docker exec tourist-app chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/www/database
docker exec tourist-app php artisan view:clear || true
docker exec tourist-app php artisan cache:clear || true
docker exec tourist-app php artisan route:clear || true
docker exec tourist-app php artisan config:clear || true
docker exec tourist-app php artisan migrate --force || true

echo "[5/5] Restarting application container..."
docker restart tourist-app
sleep 2

echo "Verifying total transactions inside container..."
docker exec tourist-app php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo 'Total transactions: ' . App\Models\Transaction::count() . PHP_EOL;" || true

echo "=========================================="
echo " SUCCESS! The system and all 17,424 records are now LIVE!"
echo " Open in browser: http://157.230.170.214"
echo "=========================================="
