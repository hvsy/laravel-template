FROM ubuntu:22.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=16
ARG POSTGRES_VERSION=14

WORKDIR /data/www/laravel

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=Asia/Shanghai

RUN echo "export LANG="zh_CN.UTF-8"" >> /etc/profile
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && \
    apt-get upgrade -y
RUN apt-get install -y software-properties-common curl build-essential \
    dos2unix gcc git libmcrypt4 libpcre3-dev memcached make \
    re2c unattended-upgrades whois vim libnotify-bin nano wget \
    debconf-utils

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /usr/share/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.2-cli php8.2-dev php8.2-fpm \
       php8.2-pgsql php8.2-sqlite3 php8.2-gd \
       php8.2-curl \
       php8.2-imap php8.2-mysql php8.2-mbstring \
       php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap \
       php8.2-intl php8.2-readline \
       php8.2-ldap \
        php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole \
        php8.2-memcached php8.2-pcov php8.2-xdebug \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -sLS https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y mysql-client \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION


RUN apt-get update \
    && apt-get install -y nginx \
    && rm -rf /etc/nginx/sites-available/default \
    && rm -rf /etc/nginx/sites-enabled/default \
    && sed -i -e"s/keepalive_timeout\s*65/keepalive_timeout 2/" /etc/nginx/nginx.conf \
    && sed -i -e"s/keepalive_timeout 2/keepalive_timeout 2;\n\tclient_max_body_size 100m/" /etc/nginx/nginx.conf \
    && echo "daemon off;" >> /etc/nginx/nginx.conf \
    && usermod -u 1000 www-data  \
    && chown -Rf www-data.www-data /var/www/html/ \
    && sed -i -e"s/worker_processes  1/worker_processes 5/" /etc/nginx/nginx.conf

# cron
RUN apt-get install -y cron rsyslog
RUN sed -i "s/^\(.\+imklog.\+\)$/\#\1/g" /etc/rsyslog.conf
RUN sed -i "s/^\\#\(cron.\+\)/\1/g" /etc/rsyslog.d/50-default.conf
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.2

RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN mkdir -p /run/php/

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY php.ini /etc/php/8.2/cli/conf.d/99-sail.ini
COPY php-fpm.ini /etc/php/8.2/fpm/conf.d/99-sail.ini

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.2/cli/conf.d/99-sail.ini

COPY cron.conf /etc/supervisor/conf.d/cron.conf
RUN  sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php/8.2/fpm/pool.d/www.conf
COPY startup.php /data/www/startup.php
RUN chmod +x /data/www/startup.php
ENV DOLLAR '$'
ENV TEMPLATE_FILE 'main'
ENV PHP_VERSION "8.2"
EXPOSE 80
RUN mkdir -p /usr/local/php/bin && ln -n /usr/bin/php /usr/local/php/bin/php

ENTRYPOINT ["/bin/bash","-c"]
CMD ["/data/www/startup.php && /usr/bin/supervisord"]
