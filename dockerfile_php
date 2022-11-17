FROM nowaja/php:7.4.8-fpm-alpine-pgsql

# Install redis

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
		&& pecl install redis \
		&& docker-php-ext-enable redis \
		&& apk del .build-deps

# Add redis to php.ini

RUN echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini