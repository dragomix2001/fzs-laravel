FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN chmod -R 755 /var/www/storage /var/www/bootstrap/cache
RUN chmod +x /var/www/docker-entrypoint.sh

EXPOSE 9000

CMD ["php-fpm"]
