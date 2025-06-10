ARG PRESTASHOP_VERSION=8.2.1
ARG PHP_VERSION=8.1
FROM prestashop/prestashop:${PRESTASHOP_VERSION}-${PHP_VERSION}

ARG PRESTASHOP_VERSION
ARG PHP_VERSION

LABEL org.opencontainers.image.source=https://github.com/bulkgate/prestasms
LABEL version=${PRESTASHOP_VERSION}-${PHP_VERSION}

# install composer with platform dependencies, xdebug
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        unzip \
        zip \
        git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug$(php -r "echo(version_compare(getenv('PHP_VERSION'), '8', '>') ? '' : '-3.1.5');") && \
    docker-php-ext-enable xdebug && \
    #https://www.jetbrains.com/help/idea/configuring-xdebug.html#537ef8ac
    echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini # this is address of default docker bridge network

COPY config-files/post-install.sh /tmp/post-install-scripts/

RUN git config --system --add safe.directory '*'

RUN chmod +x /tmp/post-install-scripts/post-install.sh

WORKDIR /var/www/html

ENV PS_DEV_MODE=1 \
    PS_INSTALL_AUTO=1

