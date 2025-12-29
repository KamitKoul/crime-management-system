FROM php:8.2-apache

# Enable Apache rewrite (common for PHP apps)
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
