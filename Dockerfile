FROM php:8.2-apache

# Disable incompatible MPMs (do NOT touch prefork)
RUN a2dismod mpm_event mpm_worker || true

# Enable required modules
RUN a2enmod rewrite

# Configure Apache to listen on Railway port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
 && sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

# Copy application
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]
