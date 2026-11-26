FROM composer:2 AS dependencies

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts \
    && php artisan package:discover --ansi

FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev libpq-dev \
    && docker-php-ext-install -j"$(nproc)" curl mbstring pdo_pgsql \
    && printf 'upload_max_filesize=6M\npost_max_size=24M\nmax_file_uploads=20\n' \
        > /usr/local/etc/php/conf.d/uploads.ini \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV LOG_CHANNEL=stderr
ENV VIEW_COMPILED_PATH=/tmp/laravel/views

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html
COPY --from=dependencies /var/www/html /var/www/html

RUN mkdir -p /tmp/laravel/views \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /tmp/laravel

EXPOSE 80

CMD ["apache2-foreground"]
