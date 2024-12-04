# Step 1: Use an official PHP image as the base image
FROM php:8.2-fpm

# Step 2: Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql

# Step 3: Install Composer (PHP dependency manager)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Step 4: Set working directory to /var/www/html
WORKDIR /var/www/html

# Step 5: Copy composer.json and composer.lock into the container
COPY composer.json composer.lock ./

# Step 6: Install PHP dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader

# Step 7: Copy the rest of the application files into the container
COPY . .

# Step 8: Expose the port the app will run on
EXPOSE 8080

# Step 9: Command to start the Slim application
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
