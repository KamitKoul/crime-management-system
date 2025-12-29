FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# Ensure uploads directory is writable
RUN mkdir -p /app/uploads/images/videos && chmod -R 777 /app/uploads

# Ensure FrankenPHP uses our Caddyfile
ENV CADDYFILE=/app/Caddyfile




