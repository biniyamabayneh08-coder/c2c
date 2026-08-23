FROM php:8.1-apache

# Install MySQL extensions for PHP so your database connections work
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all your project files into the web server directory
COPY . /var/www/html/

# Expose port 80 for web traffic
EXPOSE 80