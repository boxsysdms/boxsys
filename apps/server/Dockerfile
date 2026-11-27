# syntax=docker/dockerfile:1
ARG PHP_IMAGE=php:8.5-fpm-alpine
ARG COMPOSER_IMAGE=composer:2.9.5


# --- Stage 1: obtain Composer for dependency builds ---
FROM ${COMPOSER_IMAGE} AS composer-binary


# --- Stage 2: compile PHP extensions outside the final image ---
FROM ${PHP_IMAGE} AS extensions

ARG REDIS_VERSION=6.3.0

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql zip bcmath \
    && pecl install redis-${REDIS_VERSION} \
    && docker-php-ext-enable redis


# --- Stage 3: shared PHP-FPM and Nginx runtime ---
FROM ${PHP_IMAGE} AS runtime

WORKDIR /var/www/html

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    LARAVEL_OPTIMIZE=1

RUN apk add --no-cache nginx libpq libzip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY deploy/docker/server/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Load after the official image's docker.conf and zz-docker.conf.
COPY deploy/docker/server/www.conf /usr/local/etc/php-fpm.d/zzz-app.conf
COPY deploy/docker/server/nginx.conf /etc/nginx/http.d/default.conf
COPY --chmod=755 deploy/docker/server/start.sh /usr/local/bin/start-server

EXPOSE 80

STOPSIGNAL SIGTERM

HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1/up || exit 1

CMD ["start-server"]


# --- Stage 4: install locked production dependencies and prepare Laravel ---
FROM runtime AS dependencies

COPY --from=composer-binary /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY apps/server/composer.json apps/server/composer.lock ./

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY apps/server/ ./

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && composer check-platform-reqs --no-dev


# --- Stage 5: add development dependencies ---
FROM dependencies AS development-dependencies

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --prefer-dist --no-interaction --no-progress


# --- Stage 6: development image with Composer and development tools ---
FROM runtime AS development

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 \
    LARAVEL_OPTIMIZE=0

COPY --from=composer-binary /usr/bin/composer /usr/local/bin/composer
COPY --from=development-dependencies --chown=www-data:www-data /var/www/html/ ./


# --- Stage 7: final production image, without Composer or build tools ---
# Production is last so plain docker build also produces the lean image.
FROM runtime AS production

COPY --from=dependencies --chown=www-data:www-data /var/www/html/ ./
