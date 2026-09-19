# ============================================================
# Étape 1 : Build PHP de base
# ============================================================
FROM php:8.2-fpm-alpine AS php_base

# Extensions PHP nécessaires
RUN apk add --no-cache \
    acl \
    fcgi \
    file \
    gettext \
    git \
    gnu-libiconv \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zlib-dev \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo_mysql \
        pdo_pgsql \
        zip \
        opcache \
        gd \
        xml \
        mbstring \
    && docker-php-ext-enable opcache

# ============================================================
# Xdebug - Code Coverage
# ============================================================
RUN apk add --no-cache --virtual .xdebug-build-deps \
        $PHPIZE_DEPS \
        linux-headers \
    && pecl channel-update pecl.php.net \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .xdebug-build-deps

RUN printf '%s\n' \
    'xdebug.mode=coverage' \
    'xdebug.start_with_request=no' \
    > /usr/local/etc/php/conf.d/xdebug.ini


# ============================================================
# Composer
# ============================================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================================
# Configuration PHP
# ============================================================
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/custom.ini

WORKDIR /var/www/html


# ============================================================
# Étape 2 : Dépendances Composer
# ============================================================
FROM php_base AS php_vendor

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction


# ============================================================
# Étape 3 : Application finale
# ============================================================
FROM php_base AS php_app

# ============================================================
# Healthcheck
# ============================================================
RUN echo "#!/bin/sh" > /usr/local/bin/docker-healthcheck \
    && echo "env -i SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1" >> /usr/local/bin/docker-healthcheck \
    && chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK \
    --interval=10s \
    --timeout=3s \
    --retries=3 \
    CMD ["docker-healthcheck"]

# ============================================================
# Utilisateur Symfony
# ============================================================
RUN addgroup -g 1000 symfony \
    && adduser -u 1000 -G symfony -s /bin/sh -D symfony

# ============================================================
# Application
# ============================================================
COPY --from=php_vendor /var/www/html/vendor ./vendor
COPY --chown=symfony:symfony . .

# ============================================================
# Permissions
# ============================================================
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R symfony:symfony var public/uploads \
    && chmod -R 775 var public/uploads

USER symfony

# ============================================================
# Autoloader + scripts
# ============================================================
RUN composer dump-autoload \
        --optimize \
        --classmap-authoritative \
    && composer run-script post-install-cmd || true

EXPOSE 9000

CMD ["php-fpm"]