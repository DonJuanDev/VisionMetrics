FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy worker files
COPY . /app
COPY ../backend/config.php /backend/config.php
COPY ../backend/integrations/ /backend/integrations/

# Health check
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD pgrep -f process_jobs.php || exit 1

# Run worker
CMD ["php", "process_jobs.php"]