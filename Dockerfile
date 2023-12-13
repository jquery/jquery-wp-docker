FROM php:8.2-apache

# Install MySQLi
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y

RUN a2enmod rewrite && \
  mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./WordPress /var/www/html

EXPOSE 80
