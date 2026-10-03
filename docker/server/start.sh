#!/bin/sh
set -eu

php artisan config:cache
php artisan route:cache
php artisan event:cache

php-fpm -D
nginx -g 'daemon off;'
