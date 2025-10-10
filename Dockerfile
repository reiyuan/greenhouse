# Use official PHP 8.2 image
FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 10000

# Run PHP's built-in server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
