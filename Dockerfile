FROM php:8.4-fpm-alpine

WORKDIR /app

# Variables d'environnement pour les dépendances
ENV PHPIZE_DEPS \
    autoconf \
    dpkg-dev dpkg \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    postgresql-dev

# Installation des dépendances système
RUN apk add --no-cache \
    # Dépendances système
    $PHPIZE_DEPS \
    # Utilitaires
    git \
    unzip \
    # Extensions PHP (nécessaires pour certaines extensions PECL)
    libzip \
    icu \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    postgresql-libs

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \
    pdo_pgsql \
    opcache \
    gd \
    bcmath \
    exif \
    pcntl \
    zip && \
    docker-php-ext-enable opcache

# Installation de l'extension Redis
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apk del .build-deps

# Installation de Node.js et npm
RUN apk add --no-cache nodejs npm

# Installation de Chromium et ChromeDriver pour Panther
RUN apk add --no-cache chromium chromium-chromedriver

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Créer un utilisateur avec le même UID/GID que l'utilisateur host
ARG UID=1000
ARG GID=1000
RUN apk add --no-cache sudo && \
    addgroup -g ${GID} appuser && \
    adduser -u ${UID} -G appuser -s /bin/sh -D appuser && \
    echo "appuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/appuser

USER appuser

# Configuration PHP personnalisée
# COPY php.ini /usr/local/etc/php/conf.d/custom.ini

CMD ["php-fpm"]

