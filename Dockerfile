FROM php:8.1-apache

# Install PostgreSQL dependencies and extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copy all project files into the web server directory
COPY . /var/www/html/

# Expose port 80 for web traffic
EXPOSE 80