FROM php:8.2-fpm-alpine as base

RUN apk add --no-cache \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    curl  # Add curl for downloading dockerize

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dockerize
RUN curl -sSL https://github.com/jwilder/dockerize/releases/download/v0.8.0/dockerize-alpine-linux-amd64-v0.8.0.tar.gz | tar -xzv -C /usr/local/bin

RUN addgroup -g 1000 dev && \
    adduser -u 1000 -G dev -h /home/dev -s /bin/sh -D dev

WORKDIR /var/www

FROM base as dev

# Install build dependencies before installing Xdebug
RUN apk add --no-cache \
    git \
    unzip \
    vim \
    bash \
    linux-headers \
    $PHPIZE_DEPS

# Install and enable Xdebug (with proper error handling)
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

# Configure Xdebug with complete coverage settings
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.coverage_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

USER dev

FROM base as prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --chown=dev:dev . /var/www

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

RUN composer dump-autoload --optimize

USER dev
