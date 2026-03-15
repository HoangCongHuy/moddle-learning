FROM php:8.4-fpm-alpine

# UID/GID passed from docker-compose to match host user
ARG UID=1000
ARG GID=1000

RUN deluser www-data \
    && addgroup -g ${GID} -S www-data \
    && adduser -u ${UID} -D -S -G www-data www-data

# System dependencies
RUN apk add --no-cache \
    bash \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    libsodium-dev

# Configure GD
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp

# Install only extensions NOT built-in by default
# Built-in already: curl, dom, fileinfo, iconv, json, mbstring, openssl,
#                   opcache, pcre, simplexml, sodium, spl, tokenizer,
#                   xml, xmlreader
RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mysqli \
        soap \
        zip

# Enable bundled extensions that need activation
RUN docker-php-ext-enable opcache

# Install Redis via PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# Create MoodleData with the correct ownership (before volume mounting).
RUN mkdir -p /var/www/moodledata \
    && chown -R www-data:www-data /var/www/moodledata \
    && chmod 775 /var/www/moodledata

# Entrypoint ensures correct permissions when the volume is mounted.
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

EXPOSE 9000
