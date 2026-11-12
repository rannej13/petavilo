FROM php:8.4-fpm-alpine

RUN apk add --no-cache nginx nodejs npm git curl zip unzip libpng-dev libjpeg-turbo-dev freetype-dev \
        oniguruma-dev libxml2-dev icu-dev libzip-dev postgresql-dev linux-headers $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd opcache intl zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ARG VITE_APP_NAME="Cat Marketplace"
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
