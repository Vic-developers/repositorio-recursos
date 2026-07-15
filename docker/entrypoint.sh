#!/bin/sh

set -e

if [ ! -f /var/www/storage/oauth-private.key ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

if [ ! -f /var/www/storage/.seeded ]; then
    php artisan db:seed --force
    touch /var/www/storage/.seeded
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
