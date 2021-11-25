ARG PHP_VERSION=7.4

FROM composer:2.1.12 as composer

FROM php:${PHP_VERSION}-cli-bullseye

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
        git=1:2.30.2-1 libzip-dev=1.7.3-1 unzip=6.0-26 zip=3.0-12 \
  && docker-php-ext-install zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*
