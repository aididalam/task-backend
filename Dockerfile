# Use PHP 7.4.33 with Alpine for a lightweight image
FROM php:7.4.33-cli-alpine

# Install required dependencies
RUN apk add --no-cache \
    bash \
    zip \
    unzip \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    mysql-client \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www

# Copy Lumen app to the container
COPY ./ ./

# Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

