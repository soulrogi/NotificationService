FROM php:8.4-fpm-alpine AS main

ARG USER_UID=1000
ARG USER_GID=1000

RUN addgroup -g ${USER_GID} -S php-group \
    && adduser -D -u ${USER_UID} -G php-group -s /bin/sh php-user

RUN apk add --no-cache \
    linux-headers \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    oniguruma-dev \
    librdkafka \
    librdkafka-dev \
    autoconf \
    automake \
    libtool \
    make \
    g++ \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    bcmath \
    zip \
    opcache

RUN pecl channel-update pecl.php.net \
    && pecl install igbinary redis rdkafka \
    && docker-php-ext-enable igbinary redis rdkafka

ENV COMPOSER_HOME="/tmp/composer"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

CMD ["php-fpm"]

FROM main AS dev

ENV PHPIZE_DEPS="autoconf make automake"

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS


COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
