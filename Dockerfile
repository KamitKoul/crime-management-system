FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# Ensure FrankenPHP uses our Caddyfile
ENV CADDYFILE=/app/Caddyfile



