FROM php:8.4-fpm-alpine

# Install packages
RUN apk add --no-cache curl git build-base zlib-dev oniguruma-dev autoconf bash icu-dev imagemagick-dev libpng-dev zip libzip-dev\
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

#zip
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

# Mysql
RUN apk add --no-cache mysql-client && docker-php-ext-install pdo_mysql

# Redis
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers

RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

RUN pecl install redis \
    && docker-php-ext-enable redis

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apk del .build-deps

# Imagick
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS imagemagick-dev \
&& pecl install imagick \
&& docker-php-ext-enable imagick \
&& apk del .build-deps

# PCOV
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
&& pecl install pcov \
&& docker-php-ext-enable pcov \
&& apk del .build-deps

#GD
RUN docker-php-ext-install gd

# Configure non-root user.
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

USER www-data

# Source code
COPY ./ /var/www
WORKDIR /var/www

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#RUN composer install --no-interaction

# PCOV ini include
COPY ./docker/php-fpm/conf.d/pcov.ini /usr/local/etc/php/conf.d/pcov.ini

# Xdebug ini include
COPY ./docker/php-fpm/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

CMD php-fpm

EXPOSE 9000
