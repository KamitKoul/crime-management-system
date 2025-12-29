FROM php:8.2-apache

# Disable all MPMs first
RUN a2dismod mpm_event mpm_worker || true

# Enable the correct MPM for PHP
RUN a2enmod mpm_prefork rewrite

# Set Railway port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
 && sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

# Copy app
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]
# Install PHP extensions if needed
# RUN docker-php-ext-install mysqli pdo pdo_mysql   
# RUN docker-php-ext-enable mysqli pdo pdo_mysql
