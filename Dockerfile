FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# Ensure uploads directory is writable
RUN mkdir -p /app/uploads/images/videos && chmod -R 777 /app/uploads

# Copy Caddyfile to the correct location
COPY Caddyfile /etc/caddy/Caddyfile

# Expose port 80
EXPOSE 80

# Explicitly start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]








