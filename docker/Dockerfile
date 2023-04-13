FROM php:8.2-cli

LABEL maintainer="Ricardo Boss"

ARG WWWGROUP=1000
ARG NODE_VERSION=18
ARG POSTGRES_VERSION=14

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && \
    apt-get install -y \
        curl \
        supervisor \
        ca-certificates \
        zip \
        unzip \
        libicu-dev \
        libssl-dev \
        libcap2-bin \
        libpq-dev \
    --no-install-recommends

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo pdo_pgsql
RUN pecl install redis && docker-php-ext-enable redis

RUN apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


RUN curl -sfL https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    chmod +x /usr/bin/composer

RUN setcap "cap_net_bind_service=+ep" /usr/local/bin/php

RUN groupadd --force -g $WWWGROUP plane
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 plane

COPY start-container.sh /usr/local/bin/start-container.sh
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini $PHP_INI_DIR/conf.d/99-plane.ini
RUN chmod +x /usr/local/bin/start-container.sh

EXPOSE 8000

ENTRYPOINT ["start-container.sh"]
