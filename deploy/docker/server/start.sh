#!/bin/sh
set -eu

mkdir -p storage/app/private storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ "${LARAVEL_OPTIMIZE:-1}" = "1" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
else
    # Do not flush the shared Redis cache when switching environments.
    php artisan config:clear
    php artisan route:clear
    php artisan event:clear
    php artisan view:clear
fi

chown -R www-data:www-data storage bootstrap/cache
nginx -t
php-fpm -t

php-fpm -F &
php_pid=$!
nginx -g 'daemon off;' &
nginx_pid=$!

shutdown() {
    trap '' TERM INT
    kill -QUIT "$nginx_pid" "$php_pid" 2>/dev/null || true
    wait "$nginx_pid" 2>/dev/null || true
    wait "$php_pid" 2>/dev/null || true
}
trap 'shutdown; exit 0' TERM INT

# BusyBox ash: stop both services if either exits; Compose can restart us.
status=0
wait -n || status=$?
shutdown
[ "$status" -ne 0 ] || status=1
exit "$status"
