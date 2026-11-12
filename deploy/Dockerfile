FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache nginx nodejs npm git curl zip unzip ffmpeg \
    && install-php-extensions pdo_pgsql pgsql redis gd intl zip bcmath exif pcntl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ARG VITE_APP_NAME="Petavilo"
ENV VITE_APP_NAME=${VITE_APP_NAME}

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY package.json package-lock.json* ./
RUN npm install --no-audit --no-fund

COPY . .

RUN rm -f .env \
    && composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules

COPY deploy/nginx.conf /etc/nginx/nginx.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY deploy/start.sh /start.sh
RUN chmod +x /start.sh \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
