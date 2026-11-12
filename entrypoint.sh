#!/bin/sh
set -e

echo "=== Tourist Car System Booting ==="

# 1. Ensure database and storage directories exist
mkdir -p /var/www/database /var/www/storage/app

# 2. If database.sqlite is empty or corrupted, seed it from database_seed.sqlite
if [ ! -s /var/www/database/database.sqlite ] || [ $(wc -c < /var/www/database/database.sqlite 2>/dev/null || echo 0) -lt 100000 ]; then
    if [ -f /var/www/database/database_seed.sqlite ]; then
        echo "Restoring local database from bundled database_seed.sqlite..."
        cp -f /var/www/database/database_seed.sqlite /var/www/database/database.sqlite
    fi
fi
touch /var/www/database/database.sqlite

# 3. Ensure full permissions
chmod -R 777 /var/www/database /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# 4. Check database connectivity (Auto-detect Supabase pooler & fallback to SQLite if PostgreSQL fails)
rm -f /tmp/pg_env.sh

DB_CHECK_RESULT=$(php -r '
    $conn = getenv("DB_CONNECTION");
    $dbUrl = getenv("DATABASE_URL") ?: getenv("DB_URL");
    $host = getenv("DB_HOST");

    if ($conn !== "pgsql" && empty($dbUrl) && empty($host)) {
        @file_put_contents("/var/www/storage/app/db_status.json", json_encode([
            "status" => "FALLBACK",
            "error" => "No DATABASE_URL or DB_HOST configured in environment",
            "time" => date("Y-m-d H:i:s")
        ]));
        echo "FALLBACK";
        exit(0);
    }

    $db   = getenv("DB_DATABASE") ?: "postgres";
    $user = getenv("DB_USERNAME") ?: "postgres";
    $pass = getenv("DB_PASSWORD") ?: "";
    $port = getenv("DB_PORT") ?: 5432;
    $ref  = null;

    if ($dbUrl) {
        if (preg_match("#^postgres(?:ql)?://([^:]+):(.*)@([^:/@]+)(?::(\d+))?(?:/(.*))?$#", $dbUrl, $m)) {
            $user = urldecode($m[1]);
            $pass = urldecode($m[2]);
            $host = $m[3];
            $port = !empty($m[4]) ? (int)$m[4] : 5432;
            $db   = !empty($m[5]) ? explode("?", $m[5])[0] : "postgres";
        } elseif ($p = parse_url($dbUrl)) {
            $host = $p["host"] ?? $host;
            $port = $p["port"] ?? $port;
            $user = isset($p["user"]) ? urldecode($p["user"]) : $user;
            $pass = isset($p["pass"]) ? urldecode($p["pass"]) : $pass;
            $db   = isset($p["path"]) ? ltrim(explode("?", $p["path"])[0], "/") : $db;
        }
    }

    // Detect Supabase project reference if available
    $combined = $host . " " . $user;
    if (preg_match("/(?:db\.|postgres\.)([a-z0-9]+)\.(?:supabase\.co|pooler\.supabase\.com)/i", $combined, $rm)) {
        $ref = $rm[1];
    } elseif (preg_match("/([a-z0-9]{20})/i", $combined, $rm)) {
        $ref = $rm[1];
    }

    // Build candidates matrix (Supabase pooler IPv4 prioritized for Render compatibility)
    $candidates = [];
    if (!empty($ref)) {
        $poolerHost = "aws-0-eu-central-1.pooler.supabase.com";
        $poolerUser = "postgres." . $ref;
        // Port 6543 (Transaction Mode) connects immediately on Supabase; port 5432 is Session Mode
        $candidates[] = ["host" => $poolerHost, "user" => $poolerUser, "port" => 6543, "ssl" => "sslmode=require"];
        $candidates[] = ["host" => $poolerHost, "user" => $poolerUser, "port" => 5432, "ssl" => "sslmode=require"];
    }

    if (!empty($host)) {
        if (!empty($port) && $port != 5432 && $port != 6543) {
            $candidates[] = ["host" => $host, "user" => $user, "port" => $port, "ssl" => "sslmode=require"];
        }
        $candidates[] = ["host" => $host, "user" => $user, "port" => 6543, "ssl" => "sslmode=require"];
        $candidates[] = ["host" => $host, "user" => $user, "port" => 5432, "ssl" => "sslmode=require"];
    }

    $connected = false;
    $winner = null;
    $lastError = "No candidate succeeded";

    foreach ($candidates as $cand) {
        $cH = $cand["host"];
        $cU = $cand["user"];
        $cP = $cand["port"];
        $cSSL = $cand["ssl"];
        try {
            $dsn = "pgsql:host={$cH};port={$cP};dbname={$db};{$cSSL};connect_timeout=4";
            $pdo = new PDO($dsn, $cU, $pass);
            $pdo->query("SELECT 1");
            $connected = true;
            $winner = $cand;
            break;
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            $lastError = "[Host: {$cH}:{$cP}, User: {$cU}]: {$err}";
            // If password authentication failed, stop trying other ports as the credentials are wrong
            if (str_contains($err, "password authentication failed")) {
                break;
            }
        }
    }

    if ($connected && $winner) {
        $encodedPass = urlencode($pass);
        $wH = $winner["host"];
        $wU = $winner["user"];
        $wP = $winner["port"];
        $wSSL = $winner["ssl"];

        $envContent = "export DB_CONNECTION=pgsql\n"
                    . "export DB_HOST=\"{$wH}\"\n"
                    . "export DB_PORT={$wP}\n"
                    . "export DB_DATABASE=\"{$db}\"\n"
                    . "export DB_USERNAME=\"{$wU}\"\n"
                    . "export DB_PASSWORD=\"{$pass}\"\n"
                    . "export DB_SSLMODE=require\n"
                    . "export DATABASE_URL=\"postgresql://{$wU}:{$encodedPass}@{$wH}:{$wP}/{$db}?sslmode=require\"\n";
        @file_put_contents("/tmp/pg_env.sh", $envContent);

        @file_put_contents("/var/www/storage/app/db_status.json", json_encode([
            "status" => "CONNECTED_PGSQL",
            "host"   => $wH,
            "port"   => $wP,
            "user"   => $wU,
            "db"     => $db,
            "time"   => date("Y-m-d H:i:s")
        ]));
        echo "OK";
        exit(0);
    } else {
        @file_put_contents("/var/www/storage/app/db_status.json", json_encode([
            "status" => "FALLBACK",
            "error"  => $lastError,
            "time"   => date("Y-m-d H:i:s")
        ]));
        echo "FALLBACK";
        exit(0);
    }
' 2>/dev/null || echo "FALLBACK")

if [ "$DB_CHECK_RESULT" = "OK" ] && [ -f /tmp/pg_env.sh ]; then
    . /tmp/pg_env.sh
    echo "=== CONNECTED SUCCESSFULLY TO SUPABASE POSTGRESQL (PERSISTENT DATABASE) ==="
    echo "Host: $DB_HOST (Port: $DB_PORT)"
else
    echo "=== WARNING: POSTGRESQL CONNECTION FAILED -> FALLING BACK TO SQLITE ==="
    cat /var/www/storage/app/db_status.json 2>/dev/null || true
    echo ""
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

# 5. Clear caches
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 6. Run migrations (creates tables on Supabase if new)
echo "Running database migrations..."
php artisan migrate --force || true

# 7. Run database seeders (seeds baseline users, makes, barcodes, etc.)
echo "Running database seeders..."
php artisan db:seed --force || true

# 8. Start web server
echo "Starting application server on port ${PORT:-8080}..."
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
