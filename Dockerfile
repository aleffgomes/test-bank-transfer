FROM php:8.1.2-apache

ENV CFLAGS="$CFLAGS -D_GNU_SOURCE"

# installing the PHP extensions/dependencies we need
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \ 
    libicu-dev \
    libxml2-dev \
    libsodium-dev \
    curl \
    zip 

RUN docker-php-ext-configure gd

RUN docker-php-ext-install \
    zip \
    gd \
    intl \
    mysqli \
    soap \
    sodium \
    sockets 

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# document root for apache
COPY docker/config/000-default.conf /etc/apache2/sites-available/000-default.conf

# mod_rewrite for URL rewrite and mod_headers for .htaccess
RUN a2enmod rewrite headers

# composer
RUN curl -sS getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN composer self-update

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# criação de usuário para utilização do app
RUN useradd -ms /bin/bash test-picpay
RUN chown -R test-picpay:test-picpay /var/www/html
USER test-picpay

COPY . .

EXPOSE 80
EXPOSE 8080
EXPOSE 13000