# Use an official PHP image with Apache as the base image.
FROM php:8.2-apache

# Set environment variables.
ENV ACCEPT_EULA=Y
LABEL maintainer="er.avinashrathod@gmail.com"

# Install system dependencies.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules required for Laravel.
RUN a2enmod rewrite

# Set the Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update the default Apache site configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Install PHP extensions.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql
RUN apt update && apt install -y unixodbc-dev gpg libzip-dev \
 && curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
 && curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list \
 && apt update \
 && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
 && pecl install sqlsrv \
 && pecl install pdo_sqlsrv \
 && docker-php-ext-install pdo opcache bcmath zip \
 && echo 'extension=sqlsrv.so' >> "$PHP_INI_DIR/php.ini" \
 && echo 'extension=pdo_sqlsrv.so' >> "$PHP_INI_DIR/php.ini" \
 && a2enmod rewrite

# Install Composer globally.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a directory for your Laravel application.
WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

# Install Laravel dependencies using Composer.
RUN composer install --no-interaction --optimize-autoloader

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 80 for Apache.
EXPOSE 80

# Start Apache web server.
CMD ["apache2-foreground"]

# You can add any additional configurations or commands required for Laravel 10 here.
