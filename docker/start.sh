#!/usr/bin/env bash
echo "Starting application..."
set -e

if [ "${APP_ENV:-production}" = "production" ] && [ "${APP_DEBUG:-false}" = "true" ]; then
  echo "WARNING: APP_DEBUG=true in production reduces performance and exposes sensitive data."
fi

# --- write DB CA certs if present (Aiven and/or TiDB) ---

write_ca_cert () {
  # $1 = raw PEM env var name, $2 = base64 env var name, $3 = output path, $4 = label
  local raw_var="$1" b64_var="$2" out_path="$3" label="$4"
  local raw_val="${!raw_var:-}"
  local b64_val="${!b64_var:-}"

  if [ -n "$raw_val" ]; then
    mkdir -p "$(dirname "$out_path")"
    echo "$raw_val" > "$out_path"
    chmod 644 "$out_path"
    echo "Wrote $label CA to $out_path"
  elif [ -n "$b64_val" ]; then
    mkdir -p "$(dirname "$out_path")"
    echo "$b64_val" | base64 -d > "$out_path"
    chmod 644 "$out_path"
    echo "Wrote $label CA (from base64) to $out_path"
  fi
}

# Aiven (kept for backward compatibility / rollback)
write_ca_cert "AIVEN_CA_CERT" "AIVEN_CA_B64" "/etc/ssl/certs/aiven-ca.pem" "Aiven"

# TiDB Serverless (requires TLS - see https://docs.pingcap.com/tidbcloud/secure-connections-to-serverless-tier-clusters)
write_ca_cert "TIDB_CA_CERT" "TIDB_CA_B64" "/etc/ssl/certs/tidb-ca.pem" "TiDB"

# If MYSQL_ATTR_SSL_CA wasn't explicitly set but a TiDB cert was just written, default to it.
if [ -z "${MYSQL_ATTR_SSL_CA:-}" ] && [ -f "/etc/ssl/certs/tidb-ca.pem" ]; then
  export MYSQL_ATTR_SSL_CA="/etc/ssl/certs/tidb-ca.pem"
  echo "MYSQL_ATTR_SSL_CA not set; defaulting to /etc/ssl/certs/tidb-ca.pem"
fi

# Fall back to the system CA bundle (covers Aiven and most managed MySQL providers)
if [ -z "${MYSQL_ATTR_SSL_CA:-}" ]; then
  export MYSQL_ATTR_SSL_CA="/etc/ssl/certs/ca-certificates.crt"
  echo "MYSQL_ATTR_SSL_CA not set; defaulting to system CA bundle at /etc/ssl/certs/ca-certificates.crt"
fi

# Check if build files exist
if [ ! -f /var/www/html/public/build/manifest.json ]; then
    echo "Warning: Vite manifest not found. Running npm build..."
    npm run build || true
fi

echo "Clearing framework caches (config, routes, views)..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan icons:clear

# Application cache (Cache::remember, etc.) is preserved by default so Valkey/Redis
# or database cache survives deploys. Set CLEAR_APP_CACHE_ON_DEPLOY=true to wipe it.
if [ "${CLEAR_APP_CACHE_ON_DEPLOY:-false}" = "true" ]; then
  echo "CLEAR_APP_CACHE_ON_DEPLOY=true — clearing application cache..."
  php artisan cache:clear
else
  echo "Skipping application cache:clear (set CLEAR_APP_CACHE_ON_DEPLOY=true to force)."
fi

echo "Caching config..."
php artisan config:cache

echo "Caching views..."
php artisan icons:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Caching events..."
php artisan event:cache

echo "Optimizing..."
php artisan optimize

# Ensure storage link exists
if [ ! -L /var/www/html/public/storage ]; then
  php artisan storage:link || true
fi

php artisan icons:clear
php artisan icons:cache

# Run migration:fresh if enabled
if [ "${MIGRATE_FRESH:-false}" = "true" ]; then
    echo "Running migration:fresh..."
    php artisan migrate:fresh --force
else
    echo "Running migrations..."
    php artisan migrate --force || echo "Migration failed or already up-to-date"
fi

# Run seeding if enabled
if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Fix permissions after all artisan commands (they run as root and create files owned by root)
echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run supervisord to manage php-fpm and nginx
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf