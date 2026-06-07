#!/usr/bin/env bash
echo "Starting application..."
set -e

if [ "${APP_ENV:-production}" = "production" ] && [ "${APP_DEBUG:-false}" = "true" ]; then
  echo "WARNING: APP_DEBUG=true in production reduces performance and exposes sensitive data."
fi

# --- write Aiven CA if present ---
CERT_PATH="/etc/ssl/certs/aiven-ca.pem"

# Accept either raw PEM in AIVEN_CA_CERT or base64 in AIVEN_CA_B64
if [ -n "${AIVEN_CA_CERT:-}" ]; then
  mkdir -p $(dirname "$CERT_PATH")
  echo "$AIVEN_CA_CERT" > "$CERT_PATH"
  chmod 644 "$CERT_PATH"
  echo "Wrote AIVEN CA to $CERT_PATH"
elif [ -n "${AIVEN_CA_B64:-}" ]; then
  mkdir -p $(dirname "$CERT_PATH")
  echo "$AIVEN_CA_B64" | base64 -d > "$CERT_PATH"
  chmod 644 "$CERT_PATH"
  echo "Wrote AIVEN CA (from base64) to $CERT_PATH"
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
