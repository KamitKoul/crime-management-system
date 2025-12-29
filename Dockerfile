FROM php:8.2-apache

# HARD RESET Apache MPMs (this is the key)
RUN rm -f /etc/apache2/mods-enabled/mpm_* \
    && rm -f /etc/apache2/mods-available/mpm_* \
    && a2enmod mpm_prefork rewrite

# Set Railway port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
 && sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

# Copy application
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]
