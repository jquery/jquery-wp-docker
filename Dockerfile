FROM php:8.2-apache

# Install MySQLi
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y

RUN a2enmod ssl && a2enmod rewrite && \
  mkdir -p /etc/apache2/ssl && \
  mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./WordPress /var/www/html

EXPOSE 80
EXPOSE 443
