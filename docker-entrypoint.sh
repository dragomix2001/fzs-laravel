#!/bin/bash

cd /var/www

composer install --no-dev --optimize-autoloader --no-interaction

php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php-fpm
