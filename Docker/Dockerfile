FROM php:7.3-apache
RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql
RUN apt update && apt install libzip-dev -y && docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY . .
COPY . /var/www/html/
CMD bash -c "composer install && apache2-foreground"