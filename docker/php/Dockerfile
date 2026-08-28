FROM ubuntu:24.04

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        unzip \
        php-cli \
        php-fpm \
        php-amqp \
        php-bcmath \
        php-curl \
        php-gd \
        php-intl \
        php-mbstring \
        php-pgsql \
        php-opcache \
        php-redis \
        php-sqlite3 \
        php-xml \
        php-xdebug \
        php-zip \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i 's|listen = /run/php/php8.3-fpm.sock|listen = 9000|' /etc/php/8.3/fpm/pool.d/www.conf \
    && sed -i 's|;clear_env = no|clear_env = no|' /etc/php/8.3/fpm/pool.d/www.conf \
    && mkdir -p /run/php /var/www/html

COPY docker/php/xdebug.ini /etc/php/8.3/mods-available/xdebug.ini

WORKDIR /var/www/html

EXPOSE 9000

CMD ["/usr/sbin/php-fpm8.3", "-F"]
