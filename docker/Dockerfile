# Use a stable and existing PHP-FPM image
FROM php:8.3-fpm

# Set the working directory to the standard web root
# This is where your code will be mounted
WORKDIR /var/www/html

# Install necessary system dependencies for PHP, Composer, and MongoDB extension
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    pkg-config \
    libsasl2-dev \
    # Clean up after installation to keep the image small
    --no-install-recommends && rm -rf /var/lib/apt/lists/*

# Install the MongoDB PHP extension using PECL
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer directly via curl
# This fetches the Composer installer and places the 'composer' executable in /usr/local/bin
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Expose port 9000 for PHP-FPM communication
EXPOSE 9000

# Command to start PHP-FPM when the container runs
CMD ["php-fpm"]