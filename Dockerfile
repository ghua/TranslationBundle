FROM php:7.0

MAINTAINER Simon V.

ENV HOME_DIR /var/www/

ENV XDEBUG_CONFIG 1
ENV XDEBUG_REMOTE_HOST "172.17.0.1"
ENV PHP_IDE_CONFIG "serverName=docker"
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=develop
ARG GROUP_NAME=develop

# Set a timezone
RUN rm /etc/localtime && ln -s /usr/share/zoneinfo/GMT /etc/localtime

# Update system and install dependencies
RUN apt-get update && apt-get install -y --force-yes g++ libicu-dev libmcrypt-dev \
 libmcrypt-dev libssl-dev git mc default-jre sudo libsqlite3-dev

# Install & configure php modules
RUN docker-php-ext-install iconv && \
    docker-php-ext-install mcrypt && \
    docker-php-ext-install zip && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install pcntl && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install intl && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install mcrypt && \
    pecl install xdebug-beta && \
    docker-php-ext-enable xdebug && \
    { echo "xdebug.remote_enable=1"; \
      echo "xdebug.remote_handler=dbgp"; \
      echo "xdebug.remote_port=9000"; \
      echo "xdebug.remote_connect_back=1"; \
      echo "xdebug.profiler_enable_trigger=1"; \
      echo "xdebug.profiler_output_dir =\"/var/www/stratact-api/app/cache/test/\""; \
      echo "xdebug.trace_enable_trigger=1"; \
      echo "xdebug.trace_output_dir=\"/var/www/stratact-api/app/cache/test/\""; } | tee /usr/local/etc/php/conf.d/xdebug.ini && \
    { echo "memory_limit=-1"; } | tee -a /usr/local/etc/php/php.ini

# Install composer
RUN cd /usr/local/bin/ && curl -sS --tlsv1 https://getcomposer.org/installer | php

# System configuration
RUN echo "StrictHostKeyChecking=no" >> /etc/ssh/ssh_config && \
    echo "%${GROUP_NAME}   ALL=(ALL:ALL) NOPASSWD:ALL" >> /etc/sudoers

# Configuring environment
RUN addgroup --gid ${GROUP_ID} ${GROUP_NAME} && \
    adduser --uid ${USER_ID} --gid ${GROUP_ID} --home ${HOME_DIR} --disabled-password --no-create-home --gecos '' ${USER_NAME}

RUN mkdir -p ${HOME_DIR}

USER ${USER_NAME}

WORKDIR ${HOME_DIR}

ENTRYPOINT /usr/local/bin/php -d memory_limit=-1 -d xdebug.remote_connect_back=0 -d xdebug.remote_host=$XDEBUG_REMOTE_HOST \
${HOME_DIR}/vendor/bin/phpunit -v
