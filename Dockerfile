# Google Classroom Clone - Docker Configuration
# Professional PHP 8 container with Apache and MySQL support

FROM php:8.1-apache

# Set metadata
LABEL maintainer="Classroom Manager Team <dev@classroom-manager.com>"
LABEL description="Google Classroom Clone - Professional classroom management system"
LABEL version="1.0.0"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    # Build essentials
    gcc \
    g++ \
    make \
    # Image processing
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    # File processing
    unzip \
    zip \
    # Database tools
    mysql-client \
    # Additional libraries
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    # Development tools
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        zip \
        intl \
        opcache

# Enable Apache modules
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod ssl \
    && a2enmod expires \
    && a2enmod deflate

# Configure PHP
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/apache.conf /etc/apache2/apache2.conf

# Set up document root
RUN echo '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set permissions
RUN mkdir -p \
    uploads/avatars \
    uploads/assignments \
    uploads/resources \
    uploads/temp \
    logs \
    cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /var/www/html/cache \
    && chmod 644 /var/www/html/.env.example

# Copy docker-specific configurations
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose ports
EXPOSE 80 443

# Set health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command
CMD ["apache2-foreground"]
