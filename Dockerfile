
FROM php:8.2-fpm


RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql


RUN pecl install redis \
    && docker-php-ext-enable redis


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


RUN apt-get install -y supervisor


COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www

COPY . .

RUN composer install

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["/usr/bin/supervisord"]
