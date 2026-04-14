FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip libicu-dev libonig-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl \
    zip \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html