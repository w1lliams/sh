FROM php:7-fpm

ENV DEBIAN_FRONTEND noninteractive

RUN docker-php-ext-install \
  pdo_mysql

WORKDIR /var/tmp
RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
RUN php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
