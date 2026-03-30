FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

# Copy only the necessary parts if we want to be strict, but for local dev with volumes, 
# we'll just set the root to /var/www/html
COPY . /var/www/html/

# For frontend service, we want the document root to be /var/www/html/frontend
# But wait, if I use volumes in docker-compose, it overrides this.
# Let's just keep it simple and use the service name.

EXPOSE 80
