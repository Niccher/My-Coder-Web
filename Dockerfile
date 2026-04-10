FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    mysqli \
    gd \
    zip \
    mbstring \
    opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Use the production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure Opcache and Logging
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'error_log=/dev/stderr'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    } > "$PHP_INI_DIR/conf.d/docker-opcache.ini"

# Redirect Apache logs to stdout/stderr
RUN ln -sf /dev/stdout /var/log/apache2/access.log && \
    ln -sf /dev/stderr /var/log/apache2/error.log

# Update Apache configuration for public document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create writable directory structure and set permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/debugbar && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 writable

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
