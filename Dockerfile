FROM php:8.2-apache

# Copy code
COPY src/ /var/www/html/src/
COPY index.php /var/www/html/
COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/
COPY .htaccess /var/www/html/
COPY .env /var/www/html/

# Install dependencies
RUN apt-get update \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && apt-get install -y \
    curl \
    zip \
    unzip

RUN a2enmod rewrite \
    && service apache2 restart

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/html/
# Install dependencies
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev