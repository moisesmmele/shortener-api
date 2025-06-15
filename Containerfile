FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libmemcached-dev \
    libssl-dev \
    zlib1g-dev \
    libcurl4-openssl-dev \
    pkg-config \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip curl

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Install Http extension
RUN pecl install raphf && docker-php-ext-enable raphf
RUN pecl install pecl_http && docker-php-ext-enable http

# Install Composer
COPY --from=docker.io/composer:latest /usr/bin/composer /usr/bin/composer
# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json ./
COPY composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY src ./src
COPY public ./public
COPY config ./config

# Copy nginx config
COPY container/dev/nginx.default.conf /etc/nginx/sites-available/default

# Copy supervisord config
COPY container/dev/supervisord.default.conf /etc/supervisor/conf.d/supervisord.conf

# Configure PHP-FPM to listen on TCP
RUN sed -i 's/listen = \/run\/php\/php8.4-fpm.sock/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/log/supervisor /run/nginx

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]