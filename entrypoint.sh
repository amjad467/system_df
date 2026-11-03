#!/bin/sh
set -e

echo "=== Tourist Car System Booting ==="

# 1. Ensure database directory exists
mkdir -p /var/www/database

# 2. If database.sqlite is empty or missing, seed it from database_seed.sqlite
if [ ! -s /var/www/database/database.sqlite ] && [ -f /var/www/database/database_seed.sqlite ]; then
    echo "Restoring database from bundled database_seed.sqlite..."
    cp /var/www/database/database_seed.sqlite /var/www/database/database.sqlite
fi
touch /var/www/database/database.sqlite

# 3. Ensure full permissions
chmod -R 777 /var/www/database /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# 4. Safe cache clear
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 5. Run migrations non-fatally
php artisan migrate --force 2>/dev/null || true

# 6. Run seeders non-fatally
php artisan db:seed --force 2>/dev/null || true

# 7. Start web server
echo "Starting application server on port ${PORT:-8080}..."
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"

