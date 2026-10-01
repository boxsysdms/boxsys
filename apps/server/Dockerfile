# Step 1: Build PHP dependencies
FROM composer:2.9.5 AS composer-builder

WORKDIR /app
COPY apps/server/composer.json apps/server/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY apps/server ./
RUN composer dump-autoload --optimize


# Step 2: Compile PHP extensions
FROM php:8.5-fpm-alpine3.21 AS php-extensions-builder

RUN apk add --no-cache autoconf g++ make libpq-dev libzip-dev oniguruma-dev

RUN docker-php-ext-install pdo_pgsql mbstring zip bcmath && \
    pecl install redis && \
    docker-php-ext-enable redis

COPY docker/server/opcache.ini /usr/local/etc/php/conf.d/opcache.ini


# Step 3: Final image
FROM php:8.5-fpm-alpine3.21

WORKDIR /var/www/html

RUN apk add --no-cache nginx libpq libzip oniguruma

COPY docker/server/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/server/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/server/start.sh /start.sh
RUN chmod +x /start.sh

COPY --from=php-extensions-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-extensions-builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY --from=composer-builder /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
