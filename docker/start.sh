#!/usr/bin/env bash
echo "Running composer"
set -e

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

composer install --no-dev --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching all"
php artisan optimize

# Ensure composer autoload is up-to-date (optional for deploy)
if [ -f /var/www/html/composer.json ]; then
  composer dump-autoload --optimize || true
fi

# Ensure storage link exists
if [ ! -L /var/www/html/public/storage ]; then
  php artisan storage:link || true
fi

# Run migrations on startup
echo "Running migrations..."
php artisan migrate --force || true

# Run supervisord to manage php-fpm and nginx
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
