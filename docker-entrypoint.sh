#!/bin/bash

cd /var/www

composer install --no-dev --optimize-autoloader --no-interaction

php artisan key:generate --force

exec php-fpm
