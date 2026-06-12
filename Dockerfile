FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql mysqli

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy everything
COPY . /var/www/html/

# Debug: List files to verify
RUN ls -la /var/www/html/
RUN ls -la /var/www/html/src/Controllers/

RUN composer install --no-interaction --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 10000