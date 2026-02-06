FROM composer:latest AS composer
FROM php:8.4-cli

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions zip && \
    apt-get clean all && \
    rm -rvf /var/lib/apt/lists/*

WORKDIR /app
