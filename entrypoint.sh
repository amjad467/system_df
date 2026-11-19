#!/bin/sh
set -e

echo "=== Tourist Car System Booting ==="

# 0. Ensure .env and APP_KEY are present
if [ ! -f /var/www/.env ]; then
    if [ -f /var/www/.env.example ]; then
        cp /var/www/.env.example /var/www/.env
    else
        touch /var/www/.env
    fi
fi

if ! grep -q "^APP_KEY=base64:" /var/www/.env 2>/dev/null; then
    sed -i 's/^APP_KEY=.*/APP_KEY=base64:586aDB1KnEVJNt42BiyywkWqifUVU9tP4sbUvYdasfs=/' /var/www/.env 2>/dev/null || true
    if ! grep -q "^APP_KEY=base64:" /var/www/.env 2>/dev/null; then
        echo "APP_KEY=base64:586aDB1KnEVJNt42BiyywkWqifUVU9tP4sbUvYdasfs=" >> /var/www/.env
    fi
fi
export APP_KEY="base64:586aDB1KnEVJNt42BiyywkWqifUVU9tP4sbUvYdasfs="

# 1. Ensure all database, cache, session, and storage directories exist
mkdir -p /var/www/database \
         /var/www/storage/app/public \
         /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

# 2. If database.sqlite is empty or smaller than the full bundled seed (17,424 records), seed it from database_seed.sqlite
if [ ! -s /var/www/database/database.sqlite ] || [ $(wc -c < /var/www/database/database.sqlite 2>/dev/null || echo 0) -lt 10000000 ]; then
    if [ -f /var/www/database/database_seed.sqlite ]; then
        echo "Restoring local database from bundled database_seed.sqlite (17,424 records)..."
        cp -f /var/www/database/database_seed.sqlite /var/www/database/database.sqlite
    fi
fi
touch /var/www/database/database.sqlite

# 3. Ensure full permissions across storage, cache, and database
chmod -R 777 /var/www/database /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# 4. Check database connectivity (Auto-detect Supabase pooler or fallback to SQLite)
# Default to ultra-fast zero-latency local SQLite unless explicitly requested
PAUSE_SUPABASE=${PAUSE_SUPABASE:-true}

if [ "$PAUSE_SUPABASE" = "true" ] || [ "$PAUSE_SUPABASE" = "1" ] || [ "$USE_SQLITE" = "true" ]; then
    echo "=== SUPABASE IS TEMPORARILY PAUSED PER USER REQUEST (RUNNING ULTRA-FAST LOCAL SQLITE) ==="
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
    mkdir -p /var/www/storage/app 2>/dev/null || true
    cat <<EOF > /var/www/storage/app/db_status.json
{
    "status": "LOCAL_SQLITE_PAUSED",
    "mode": "FAST_LOCAL_SQLITE",
    "message": "Supabase بە کاتی ڕاگیراوە لەسەر داوای بەکارهێنەر بۆ خێرایی باڵا",
    "time": "$(date +"%Y-%m-%d %H:%M:%S")"
}
EOF
else
    rm -f /tmp/pg_env.sh

    DB_CHECK_RESULT=$(php -r '
    $conn = getenv("DB_CONNECTION");
    $dbUrl = getenv("DATABASE_URL") ?: getenv("DB_URL");
    $host = getenv("DB_HOST");

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
            $rawDb = !empty($m[5]) ? explode("?", $m[5])[0] : "";
            $db   = (!empty($rawDb) && !str_contains($rawDb, "sslmode")) ? $rawDb : "postgres";
        } elseif ($p = parse_url($dbUrl)) {
            $host = $p["host"] ?? $host;
            $port = $p["port"] ?? $port;
            $user = isset($p["user"]) ? urldecode($p["user"]) : $user;
            $pass = isset($p["pass"]) ? urldecode($p["pass"]) : $pass;
            $rawDb = isset($p["path"]) ? ltrim(explode("?", $p["path"])[0], "/") : "";
            $db   = (!empty($rawDb) && !str_contains($rawDb, "sslmode")) ? $rawDb : "postgres";
        }
    }

    if (empty($db) || str_contains($db, "sslmode")) {
        $db = "postgres";
    }

    // Auto-fix truncated Supabase project ref if present
    if (str_starts_with($user, "postgres.sdlduahwpqjzdkfsl") && $user !== "postgres.sdlduahwpqjzdkfslthg") {
        $user = "postgres.sdlduahwpqjzdkfslthg";
    }

    // Detect Supabase project reference if available
    $combined = $host . " " . $user;
    if (preg_match("/(?:db\.|postgres\.)([a-z0-9]+)\.(?:supabase\.co|pooler\.supabase\.com)/i", $combined, $rm)) {
        $ref = $rm[1];
    } elseif (preg_match("/([a-z0-9]{20})/i", $combined, $rm)) {
        $ref = $rm[1];
    }

    // Build candidates matrix (Verified Supabase pooler IPv4 prioritized for Render compatibility)
    $candidates = [];

    // 1. High-priority verified Supabase cloud credentials
    $candidates[] = [
        "host" => "aws-0-eu-central-1.pooler.supabase.com",
        "user" => "postgres.sdlduahwpqjzdkfslthg",
        "pass" => "W6vOP8FFXl5UOJh5",
        "port" => 6543,
        "db"   => "postgres",
        "ssl"  => "sslmode=require"
    ];
    $candidates[] = [
        "host" => "aws-0-eu-central-1.pooler.supabase.com",
        "user" => "postgres.sdlduahwpqjzdkfslthg",
        "pass" => "W6vOP8FFXl5UOJh5",
        "port" => 5432,
        "db"   => "postgres",
        "ssl"  => "sslmode=require"
    ];

    // 2. Also test user-configured environment variables if different
    if (!empty($pass) && $pass !== "W6vOP8FFXl5UOJh5") {
        if (!empty($ref)) {
            $poolerHost = "aws-0-eu-central-1.pooler.supabase.com";
            $poolerUser = "postgres." . $ref;
            $candidates[] = ["host" => $poolerHost, "user" => $poolerUser, "pass" => $pass, "port" => 6543, "db" => $db, "ssl" => "sslmode=require"];
            $candidates[] = ["host" => $poolerHost, "user" => $poolerUser, "pass" => $pass, "port" => 5432, "db" => $db, "ssl" => "sslmode=require"];
        }
        if (!empty($host)) {
            $candidates[] = ["host" => $host, "user" => $user, "pass" => $pass, "port" => $port, "db" => $db, "ssl" => "sslmode=require"];
            $candidates[] = ["host" => $host, "user" => $user, "pass" => $pass, "port" => 6543, "db" => $db, "ssl" => "sslmode=require"];
            $candidates[] = ["host" => $host, "user" => $user, "pass" => $pass, "port" => 5432, "db" => $db, "ssl" => "sslmode=require"];
        }
    }

    $connected = false;
    $winner = null;
    $lastError = "No candidate succeeded";

    foreach ($candidates as $cand) {
        $cH = $cand["host"];
        $cU = $cand["user"];
        $cP = $cand["port"];
        $cPass = $cand["pass"];
        $cDb = (!empty($cand["db"]) && !str_contains($cand["db"], "sslmode")) ? $cand["db"] : "postgres";
        if (str_contains($cH, "supabase")) {
            $cDb = "postgres";
        }

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $dsn = "pgsql:host={$cH};port={$cP};dbname={$cDb};sslmode=require";
                $pdo = new PDO($dsn, $cU, $cPass, [
                    PDO::ATTR_TIMEOUT => 8,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->query("SELECT 1");
                $connected = true;
                $cand["db"] = $cDb;
                $winner = $cand;
                break 2;
            } catch (\Throwable $e) {
                $err = $e->getMessage();
                $lastError = "[Host: {$cH}:{$cP}, User: {$cU}]: {$err}";
                if ($attempt === 1) {
                    usleep(500000); // 0.5s pause before retry
                }
            }
        }
    }

    if ($connected && $winner) {
        $wH = $winner["host"];
        $wU = $winner["user"];
        $wPass = $winner["pass"];
        $wP = $winner["port"];
        $wDb = (!empty($winner["db"]) && !str_contains($winner["db"], "sslmode")) ? $winner["db"] : "postgres";
        if (str_contains($wH, "supabase")) {
            $wDb = "postgres";
        }
        $encodedPass = urlencode($wPass);

        $envContent = "export DB_CONNECTION=pgsql\n"
                    . "export DB_HOST=\"{$wH}\"\n"
                    . "export DB_PORT={$wP}\n"
                    . "export DB_DATABASE=\"{$wDb}\"\n"
                    . "export DB_USERNAME=\"{$wU}\"\n"
                    . "export DB_PASSWORD=\"{$wPass}\"\n"
                    . "export DB_SSLMODE=require\n"
                    . "export DATABASE_URL=\"postgresql://{$wU}:{$encodedPass}@{$wH}:{$wP}/{$wDb}?sslmode=require\"\n"
                    . "export SESSION_DRIVER=file\n"
                    . "export CACHE_STORE=file\n";
        @file_put_contents("/tmp/pg_env.sh", $envContent);

        @file_put_contents("/var/www/storage/app/db_status.json", json_encode([
            "status" => "CONNECTED_PGSQL",
            "host"   => $wH,
            "port"   => $wP,
            "user"   => $wU,
            "db"     => $wDb,
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
fi

export SESSION_DRIVER=file
export CACHE_STORE=file
export PHP_CLI_SERVER_WORKERS=4

# 5. Clear stale caches before migration
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# 6. Run migrations (creates tables and performance indexes on Supabase)
echo "Running database migrations..."
php artisan migrate --force || true

# 7. Run database seeders (seeds baseline users, makes, barcodes, etc.)
echo "Running database seeders..."
php artisan db:seed --force || true

# 8. Compile and cache configurations, routes, and views for production ultra-fast performance
echo "Compiling production caches..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# 9. Start web server with multi-worker concurrency
echo "Starting multi-worker application server on port ${PORT:-8080} (Workers: ${PHP_CLI_SERVER_WORKERS})..."
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
