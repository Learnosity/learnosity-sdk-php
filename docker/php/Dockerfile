ARG PHP_VERSION=8.3
ARG DEBIAN_VERSION=bookworm
ARG COMPOSER_VERSION=2.7.6

FROM composer:${COMPOSER_VERSION} as composer

FROM php:${PHP_VERSION}-fpm-${DEBIAN_VERSION}

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Install nginx and other dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        git=1:2.* \
        libzip-dev=1.* \
        unzip=6.0* \
        zip=3.0* \
        nginx=1.22.* \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP-FPM to listen on TCP/IP
RUN sed -i 's/listen = \/run\/php-fpm.sock/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf

# Configure nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default

WORKDIR /var/www/html

# Copy start script
COPY <<'EOF' /usr/local/bin/start.sh
#!/bin/sh
php-fpm -D
nginx -g 'daemon off;'
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]
