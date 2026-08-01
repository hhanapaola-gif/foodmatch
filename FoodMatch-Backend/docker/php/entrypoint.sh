#!/bin/bash
set -e

if [ "$APP_KEY" = "" ]; then
    echo "APP_KEY is not set. Refusing to start." >&2
    exit 1
fi

# storage/ is volume-mounted (so logs survive container recreation), which
# hides the subdirectories baked into the image at build time. Recreate them
# idempotently on every boot so Laravel's file cache/view compiler doesn't
# fail on a fresh volume.
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage

# Only one replica (laravel-app-1) runs migrations/cache warmup, so the
# two load-balanced app containers don't race each other on boot.
if [ "$CONTAINER_ROLE" = "primary" ]; then
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan passport:keys --force || true

    # passport:keys only generates the RSA key pair — it does NOT create the
    # OAuth "personal access client" row that createToken() (used by every
    # login/registration) needs. Without this, a fresh database makes every
    # login/registration fail with "Personal access client not found."
    # Guarded so container restarts don't pile up duplicate client rows.
    HAS_PERSONAL_CLIENT=$(php artisan tinker --execute="echo (int) \Laravel\Passport\Client::where('personal_access_client', 1)->exists();" 2>/dev/null | tail -1)
    if [ "$HAS_PERSONAL_CLIENT" != "1" ]; then
        php artisan passport:client --personal --name="FoodMatch Personal Access Client" -n
    fi
fi

exec "$@"
