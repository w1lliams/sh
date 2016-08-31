FROM php:7-fpm

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    php5-dev \
    gcc

RUN docker-php-ext-install \
  pdo_mysql

COPY php.ini /usr/local/etc/php/

WORKDIR /var/tmp
RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer


COPY morpher /var/tmp/morpher/
RUN cd /var/tmp/morpher/php; bash build.sh
RUN docker-php-ext-enable /var/tmp/morpher/php/modules/morpher.so