#!/bin/sh
set -e

echo "=== Tourist Car System Booting ==="

# 1. Ensure database directory exists
mkdir -p /var/www/database

# 2. If database.sqlite is empty or corrupted, seed it from database_seed.sqlite
if [ ! -s /var/www/database/database.sqlite ] || [ $(wc -c < /var/www/database/database.sqlite 2>/dev/null || echo 0) -lt 100000 ]; then
    if [ -f /var/www/database/database_seed.sqlite ]; then
        echo "Restoring database from bundled database_seed.sqlite..."
        cp -f /var/www/database/database_seed.sqlite /var/www/database/database.sqlite
    fi
fi
touch /var/www/database/database.sqlite

# 3. Ensure full permissions
chmod -R 777 /var/www/database /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# 4. Check database connectivity (Auto-fallback to SQLite if PostgreSQL cannot connect)
DB_FALLBACK=$(php -r '
    $conn = getenv("DB_CONNECTION");
    $dbUrl = getenv("DATABASE_URL") ?: getenv("DB_URL");
    $host = getenv("DB_HOST");
    if ($dbUrl) {
        $p = parse_url($dbUrl);
        $host = $p["host"] ?? $host;
    }
    if ($conn === "pgsql" || $dbUrl) {
        try {
            $port = (isset($p["port"]) ? $p["port"] : getenv("DB_PORT")) ?: 5432;
            $db = (isset($p["path"]) ? ltrim($p["path"], "/") : getenv("DB_DATABASE")) ?: "postgres";
            $user = (isset($p["user"]) ? urldecode($p["user"]) : getenv("DB_USERNAME")) ?: "postgres";
            $pass = (isset($p["pass"]) ? urldecode($p["pass"]) : getenv("DB_PASSWORD")) ?: "";
            $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$db};sslmode=prefer;connect_timeout=10", $user, $pass);
            $pdo->query("SELECT 1");
        } catch (\Throwable $e) {
            echo "FALLBACK";
            exit(0);
        }
    }
    echo "OK";
' 2>/dev/null || echo "FALLBACK")

if [ "$DB_FALLBACK" = "FALLBACK" ]; then
    echo "PostgreSQL host unreachable or invalid. Falling back cleanly to bundled SQLite database..."
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/database/database.sqlite
    unset DATABASE_URL
    unset DB_URL
    unset DB_HOST
    unset DB_PORT
    unset DB_USERNAME
    unset DB_PASSWORD
    if [ -f /var/www/.env ]; then
        sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' /var/www/.env 2>/dev/null || true
        sed -i 's/^DATABASE_URL=/#DATABASE_URL=/' /var/www/.env 2>/dev/null || true
        sed -i 's/^DB_URL=/#DB_URL=/' /var/www/.env 2>/dev/null || true
        sed -i 's/^DB_HOST=/#DB_HOST=/' /var/www/.env 2>/dev/null || true
    fi
fi

# 5. Safe cache clear
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 6. Run migrations non-fatally
php artisan migrate --force 2>/dev/null || true

# 7. Run seeders non-fatally
php artisan db:seed --force 2>/dev/null || true

# 8. Start web server
echo "Starting application server on port ${PORT:-8080}..."
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
