FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# FrankenPHP automatically uses the Caddyfile if present in the working directory
# or we can be explicit if needed, but default behavior with /app works well.

