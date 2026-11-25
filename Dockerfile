############################################
# Frontend assets (production only)
############################################
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .

ARG VITE_APP_NAME
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME


RUN npm run build

############################################
# Base image (shared by development and production)
############################################
FROM serversideup/php:8.4-fpm-nginx AS base

USER root
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath gd zip
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
USER www-data
RUN composer install --no-interaction --no-dev --optimize-autoloader \
    --prefer-dist --no-scripts
USER root
COPY --chown=www-data:www-data . .
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
RUN echo "upload_tmp_dir = /tmp" > /usr/local/etc/php/conf.d/upload_tmp_dir.ini
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
USER www-data
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8080/up || exit 1

############################################
# Development image
# Remaps www-data to match host UID/GID so
# bind-mounted files stay owned by you.
############################################
FROM base AS development

USER root

ARG USER_ID
ARG GROUP_ID

RUN sed -i "s/^www-data:x:33:33:/www-data:x:${USER_ID}:${GROUP_ID}:/" /etc/passwd && \
    sed -i "s/^www-data:x:33:/www-data:x:${GROUP_ID}:/" /etc/group && \
    for path in \
        /composer \
        /etc/nginx \
        /etc/ssl/private \
        /run \
        /usr/local/etc/php-fpm.conf \
        /usr/local/etc/php-fpm.d/zzz-docker-php-serversideup-fpm-debug.conf \
        /var/cache/nginx \
        /var/log/nginx \
        /usr/local/etc/php/conf.d/zzz-serversideup-docker-php-debug.ini; do \
        [ -e "$path" ] && chown -R www-data:www-data "$path"; \
    done

USER www-data

############################################
# Production image
# Includes compiled frontend assets.
############################################
FROM base AS production

COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
