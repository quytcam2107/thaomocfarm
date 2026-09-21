FROM php:8.3-fpm

# [FIX] Nâng cấp Node lên 22 để tương thích với NPM v12+
ARG NODE_VERSION=22

WORKDIR /var/www/html

# Cài đặt system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình và cài GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Cài đặt các PHP extensions yêu cầu cho Laravel
RUN docker-php-ext-install pdo_mysql mbstring bcmath exif pcntl intl zip opcache

# Cài đặt Redis extension qua PECL
RUN pecl install redis && docker-php-ext-enable redis

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cài đặt Node.js và NPM (Đã dùng Node 22)
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Phân quyền thư mục
RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]