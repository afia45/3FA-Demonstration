FROM php:8.2-apache

# Install system dependencies and Composer
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libmagickwand-dev \
    && docker-php-ext-install zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install the MySQLi extension
RUN docker-php-ext-install mysqli

# Set the working directory
WORKDIR /var/www/html

# Copy the composer.json and composer.lock files to the container
COPY ./www/composer.json /var/www/html/

# Set the COMPOSER_ALLOW_SUPERUSER environment variable
ENV COMPOSER_ALLOW_SUPERUSER 1

# Install project dependencies
RUN composer install --no-scripts --no-autoloader --ignore-platform-req=ext-sockets

# Copy the rest of the application files
COPY ./www /var/www/html/

# Generate the autoloader
RUN composer dump-autoload --optimize

# Enable Apache rewrite module
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]