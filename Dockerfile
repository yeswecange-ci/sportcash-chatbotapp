# ── Stage 1: Build assets ──────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ── Stage 2: PHP app ──────────────────────────────
FROM php:8.3-cli

# Install only the system libs and extensions NOT already in the base image
# php:8.3-cli already includes: mbstring, xml, curl, ctype, tokenizer, pdo, json, etc.
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath zip gd intl pcntl fileinfo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock* ./
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application code
COPY . .

# Re-run composer scripts (post-autoload-dump)
RUN COMPOSER_MEMORY_LIMIT=-1 composer dump-autoload --optimize --no-scripts

# Copy built assets from stage 1
COPY --from=assets /app/public/build public/build

# Create storage directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/app/private/imports \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start command: migrate + queue worker (background) + serve
CMD php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan queue:work --tries=3 --timeout=90 & \
    while true; do sleep 60 && php artisan schedule:run --no-interaction 2>&1; done & \
    php artisan serve --host=0.0.0.0 --port=8080
