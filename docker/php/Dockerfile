FROM php:8.2-fpm-bullseye

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libkrb5-dev \
    libc-client2007e-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        intl \
        zip \
        exif \
        bcmath \
        gd \
        imap

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
