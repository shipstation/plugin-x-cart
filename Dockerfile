FROM php:8.0-apache

ARG COMPOSER_VERSION
ENV COMPOSER_VERSION=${COMPOSER_VERSION:-2.4.4}
ARG XCART_VERSION
ENV XCART_VERSION=${XCART_VERSION:-5.5.0.13}
ENV XCART_HOME=/var/www/xcart
ENV APACHE_DOCUMENT_ROOT=${XCART_HOME}/public

RUN docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-install ctype pcntl pdo_mysql sockets

RUN curl -Lo /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 && \
    chmod +x /usr/local/bin/mhsendmail && \
    echo "sendmail_path=\"/usr/local/bin/mhsendmail  --smtp-addr='mail:1025'\"" > /usr/local/etc/php/conf.d/sendmail.ini

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY ./docker/docker-php.conf /etc/apache2/conf-available/docker-php.conf

WORKDIR ${XCART_HOME}

RUN curl -Lo composer.phar https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar && \
    curl -sLo composer.phar.sha256sum https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar.sha256sum && \
    sha256sum --check composer.phar.sha256sum && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

RUN curl -sLo - https://my.x-cart.com/storage/nfr_distributives/x-cart-${XCART_VERSION}-en.tgz | tar -C ../ -zxvf -
COPY ./docker/env.docker .env.local

COPY ./modules/ShipStation ./modules/ShipStation

RUN chown -R www-data ${XCART_HOME}
