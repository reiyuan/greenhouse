# Use PHP with Apache
FROM php:8.2-apache

# Copy project files
COPY . /var/www/html/

# Enable mysqli for MySQL
RUN docker-php-ext-install mysqli

# Expose default port
EXPOSE 80
