FROM ubuntu:24.04

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        software-properties-common \
        unzip \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php8.4-cli \
        php8.4-fpm \
        php8.4-amqp \
        php8.4-bcmath \
        php8.4-curl \
        php8.4-gd \
        php8.4-intl \
        php8.4-mbstring \
        php8.4-pgsql \
        php8.4-opcache \
        php8.4-redis \
        php8.4-sqlite3 \
        php8.4-xml \
        php8.4-xdebug \
        php8.4-zip \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i 's|listen = /run/php/php8.4-fpm.sock|listen = 9000|' /etc/php/8.4/fpm/pool.d/www.conf \
    && sed -i 's|;clear_env = no|clear_env = no|' /etc/php/8.4/fpm/pool.d/www.conf \
    && mkdir -p /run/php /var/www/html

COPY docker/php/xdebug.ini /etc/php/8.4/mods-available/xdebug.ini

WORKDIR /var/www/html

EXPOSE 9000

CMD ["/usr/sbin/php-fpm8.4", "-F"]
