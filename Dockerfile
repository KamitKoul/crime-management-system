FROM dunglas/frankenphp

# Install mysqli extension
RUN install-php-extensions mysqli

# Copy application files
COPY . /app
WORKDIR /app

# Tell FrankenPHP to listen on the port provided by Railway and disable TLS
ENV SERVER_NAME=:80


