# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci || npm install

COPY . .
RUN npm run build

# ==========================================
# Stage 2: Production PHP-FPM + Nginx Environment
# ==========================================
FROM php:8.4-fpm-alpine

# Install system dependencies (including git, unzip, zip required by Composer)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Production PHP settings
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "cgi.fix_pathinfo=0" > "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "upload_max_filesize = 64M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "post_max_size = 64M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/conf.d/custom.ini"

WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Install Composer dependencies (use --no-scripts to prevent artisan discovery during image build without env vars)
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Copy configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

# Ensure proper permissions and fix CRLF on script
RUN sed -i 's/\r$//' /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh \
    && mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
