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
    curl \
    git \
    unzip \
    shadow

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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sSL https://github.com/jwilder/dockerize/releases/download/v0.8.0/dockerize-alpine-linux-amd64-v0.8.0.tar.gz | tar -xzv -C /usr/local/bin

RUN addgroup -g 1000 dev && \
    adduser -u 1000 -G dev -h /home/dev -s /bin/sh -D dev && \
    mkdir -p /var/www/vendor && \
    chown -R dev:dev /var/www

WORKDIR /var/www

FROM base as dev

RUN apk add --no-cache \
    vim \
    bash \
    linux-headers \
    $PHPIZE_DEPS

RUN pecl channel-update pecl.php.net && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.coverage_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Make sure dev user owns everything
RUN chown -R dev:dev /var/www

USER dev

FROM base as prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

USER dev
