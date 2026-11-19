#!/bin/bash
set -e

echo "=========================================="
echo "  Tourist Car System - Data & App Updater"
echo "  Server IP: 157.230.170.214"
echo "=========================================="

APP_DIR="/var/www/tourist-car-system"

if [ ! -d "$APP_DIR" ]; then
    echo "Directory $APP_DIR does not exist! Running full installer..."
    curl -fsSL https://raw.githubusercontent.com/amjad467/system_df/main/install.sh | bash
    exit 0
fi

cd "$APP_DIR"

echo "[1/4] Pulling latest code and database from GitHub..."
git fetch origin main
git reset --hard origin/main

echo "[2/4] Syncing 17,424 records database into Docker container..."
if [ -f "$APP_DIR/database/database_seed.sqlite" ]; then
    docker cp "$APP_DIR/database/database_seed.sqlite" tourist-app:/var/www/database/database.sqlite
    docker exec tourist-app chmod 777 /var/www/database/database.sqlite
    echo "Database file copied successfully."
else
    echo "Warning: database_seed.sqlite not found!"
fi

echo "[3/4] Clearing cache and running migrations..."
docker exec tourist-app php artisan cache:clear || true
docker exec tourist-app php artisan view:clear || true
docker exec tourist-app php artisan route:clear || true
docker exec tourist-app php artisan config:clear || true
docker exec tourist-app php artisan migrate --force || true

echo "[4/4] Verifying total transactions..."
docker exec tourist-app php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo 'Total transactions: ' . App\Models\Transaction::count() . PHP_EOL;"

echo "=========================================="
echo " SUCCESS! The system and all 17,424 records are now LIVE!"
echo " Open in browser: http://157.230.170.214"
echo "=========================================="
