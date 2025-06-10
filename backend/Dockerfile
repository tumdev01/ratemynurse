FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip unzip curl git libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY ./laravel /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
