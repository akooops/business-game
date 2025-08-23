#!/usr/bin/env bash
set -e

# Configure Apache to listen on $PORT if provided by the platform (e.g., Render)
if [ -n "${PORT}" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true
  sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf || true
fi

# Ensure storage symlink exists (non-fatal if already linked)
php artisan storage:link || true

# Cache configuration and routes/views (non-fatal if closures prevent caching)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations
php artisan migrate --force --no-interaction

# Optionally seed demo data (set SEED_DEMO=true to enable)
if [ "${SEED_DEMO}" = "true" ] || [ "${SEED_DEMO}" = "1" ]; then
  php artisan db:seed --force --no-interaction
fi

exec apache2-foreground


