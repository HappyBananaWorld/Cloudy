FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN composer global require laravel/installer

CMD ["php-fpm"]
