FROM php:8.4-cli-alpine

RUN apk add --no-cache  \
    supervisor  \
    postgresql-libs \
    postgresql-dev \
    linux-headers \
    libzip-dev \
    librdkafka-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql bcmath zip \
    && apk add --no-cache autoconf build-base \
    && pecl install igbinary redis rdkafka \
    && docker-php-ext-enable igbinary redis rdkafka \
    && apk del autoconf build-base

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/supervisor/notifications.conf /etc/supervisor/conf.d/notifications.conf

RUN mkdir -p /var/log/supervisor /var/run/supervisor

WORKDIR /var/www

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
