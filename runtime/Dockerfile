FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install opentelemetry \
    && docker-php-ext-enable opentelemetry

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY app/composer.json ./composer.json
RUN composer install --no-interaction --prefer-dist --no-progress

COPY app/ ./

COPY runtime/zz-otel.conf /usr/local/etc/php-fpm.d/zz-otel.conf

EXPOSE 9000

CMD ["php-fpm"]
