FROM php:8.2-apache
COPY --from=composer:2.1.8 /usr/bin/composer /usr/local/bin/composer

# Install dependencies
RUN apt-get update \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && apt-get install -y \
    curl \
    zip \
    unzip

# Enable apache modules
# Routing
RUN a2enmod rewrite \
    && service apache2 restart

# Copy code
COPY src/ /var/www/html/src/
COPY public/ /var/www/html/public/
COPY index.php /var/www/html/
COPY composer.json /var/www/html/
COPY .htaccess /var/www/html/
COPY .env /var/www/html/

WORKDIR /var/www/html/

# Install dependencies
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev