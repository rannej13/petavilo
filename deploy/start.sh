#!/bin/sh
set -e

ROLE="${CONTAINER_ROLE:-web}"

cd /var/www/html

mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage
mkdir -p /tmp/nginx/body /tmp/nginx/fastcgi /tmp/nginx/proxy /tmp/nginx/uwsgi /tmp/nginx/scgi
chown -R www-data:www-data /tmp/nginx /var/lib/nginx 2>/dev/null || true

if [ "$ROLE" = "worker" ]; then
    exec su www-data -s /bin/sh -c "php artisan queue:work --sleep=3 --tries=3 --max-time=3600"
fi

if [ "$ROLE" = "scheduler" ]; then
    exec su www-data -s /bin/sh -c "php artisan schedule:work"
fi

until php artisan migrate --force; do
    echo "Waiting for the database..."
    sleep 3
done

php artisan migrate --path=database/settings --force
php artisan settings:clear-cache || true
php artisan settings:clear-discovered || true
php artisan catmarket:install
php artisan catmarket:images || true
php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan event:cache || true

php-fpm -D
exec nginx -g 'daemon off;'
