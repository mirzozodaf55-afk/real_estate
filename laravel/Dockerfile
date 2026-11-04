# API Laravel для бесплатного Render (render.yaml в корне репозитория).
# Apache + PHP 8.2; порт берётся из переменной PORT, которую задаёт Render.
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev libzip-dev libicu-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql zip intl bcmath exif pcntl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# лимиты под загрузку фото и видео в чат (как в проверках контроллеров)
RUN { echo 'upload_max_filesize=50M'; echo 'post_max_size=60M'; echo 'memory_limit=256M'; } > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# сначала только зависимости — при правке кода Docker не переустанавливает их заново
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
        public/large public/medium public/small public/xsmall public/avatar public/filemessage/original \
    && chown -R www-data:www-data storage bootstrap/cache public

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["sh", "/var/www/html/docker/entrypoint.sh"]
