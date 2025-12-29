FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# Ensure uploads directory is writable
RUN mkdir -p /app/uploads/images/videos && chmod -R 777 /app/uploads

# Copy Caddyfile to the default location where FrankenPHP looks for it
COPY Caddyfile /etc/caddy/Caddyfile





