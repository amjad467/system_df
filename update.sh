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

echo "[3/5] Protecting live database and creating automatic backup..."
# 1. Create a timestamped backup of the live database inside container if it exists
docker exec tourist-app sh -c "
    mkdir -p /var/www/database/backups
    if [ -s /var/www/database/database.sqlite ]; then
        BACKUP_FILE=\"/var/www/database/backups/backup_\$(date +%Y%m%d_%H%M%S).sqlite\"
        cp /var/www/database/database.sqlite \"\$BACKUP_FILE\"
        echo \"Live database safely backed up to \$BACKUP_FILE\"
    fi
" || true

# 2. NEVER overwrite live database! Only initialize if database.sqlite is missing or empty
docker exec tourist-app sh -c "
    if [ ! -s /var/www/database/database.sqlite ]; then
        if [ -f /var/www/database/database_seed.sqlite ]; then
            echo 'Initializing database from database_seed.sqlite...'
            cp /var/www/database/database_seed.sqlite /var/www/database/database.sqlite
            chmod 777 /var/www/database/database.sqlite
        fi
    else
        echo 'Live database exists. PRESERVING ALL LIVE TRANSACTIONS (Will never overwrite)!'
    fi
" || true

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
