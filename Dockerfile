# ---- vendor: install PHP deps with composer (PHP 8.2 to match runtime + composer.lock) ----
FROM serversideup/php:8.2-cli AS vendor
USER root
WORKDIR /app
COPY . .
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction

# ---- runtime: app + baked vendor on serversideup ----
FROM serversideup/php:8.2-fpm-nginx

USER root

# App + vendor baked into the image
COPY --chown=www-data:www-data --from=vendor /app /var/www/html

# Recreate writable runtime dirs and the bind-mount targets, then hand them to www-data
RUN rm -rf /var/www/html/storage/logs/* /var/www/html/bootstrap/cache/*.php \
    && mkdir -p \
        /var/www/html/storage/app/gpx \
        /var/www/html/storage/app/db \
        /var/www/html/storage/app/public \
        /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
        /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data
