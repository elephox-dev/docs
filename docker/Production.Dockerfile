FROM php:8.2-cli

LABEL maintainer="Ricardo Boss"

ARG WWWGROUP=1000

WORKDIR /var/www/html

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
    --no-install-recommends && \
    apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sfL https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    chmod +x /usr/bin/composer

RUN setcap "cap_net_bind_service=+ep" /usr/local/bin/php

RUN groupadd --force -g $WWWGROUP plane
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 plane

COPY . .
RUN composer install --no-dev --no-interaction

COPY ./docker/start-container.sh /usr/local/bin/start-container.sh
RUN chmod +x /usr/local/bin/start-container.sh

COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/php.ini $PHP_INI_DIR/conf.d/99-plane.ini

EXPOSE 8000

ENTRYPOINT ["start-container.sh"]
