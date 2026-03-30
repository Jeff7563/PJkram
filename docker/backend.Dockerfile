FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

COPY . /var/www/html/

EXPOSE 80
