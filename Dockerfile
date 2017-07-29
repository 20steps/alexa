FROM php:7.0-apache

# PHP extensions
ENV APCU_VERSION 5.1.5
RUN buildDeps=" \
        libicu-dev \
        zlib1g-dev \
    " \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        $buildDeps \
        libicu52 \
        zlib1g \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo_mysql \
        pcntl \
        zip \
    && apt-get purge -y --auto-remove $buildDeps
RUN pecl install \
        apcu-$APCU_VERSION \
    && docker-php-ext-enable --ini-name 05-opcache.ini \
        opcache \
    && docker-php-ext-enable --ini-name 20-apcu.ini \
        apcu

# Start of intall APCu-BC extension
ADD https://pecl.php.net/get/apcu_bc-1.0.3.tgz /tmp/apcu_bc.tar.gz
RUN mkdir -p /usr/src/php/ext/apcu-bc\
  && tar xf /tmp/apcu_bc.tar.gz -C /usr/src/php/ext/apcu-bc --strip-components=1

RUN docker-php-ext-configure apcu-bc\
  && docker-php-ext-install apcu-bc

RUN rm -rd /usr/src/php/ext/apcu-bc && rm /tmp/apcu_bc.tar.gz

RUN rm /usr/local/etc/php/conf.d/docker-php-ext-apc.ini
RUN echo extension=apc.so > /usr/local/etc/php/conf.d/21-apc.ini
# End of install APCu-BC extension

# Apache config
RUN a2enmod rewrite
ADD docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# PHP config
ADD docker/php/php.ini /usr/local/etc/php/php.ini

# Install Git
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
    && rm -rf /var/lib/apt/lists/*

# Add the application
ADD . /app
WORKDIR /app

# Install composer
RUN ./docker/composer.sh \
    && mv composer.phar /usr/bin/composer \
    && composer global require "hirak/prestissimo:^0.3"

RUN \
    # Remove var directory if it's accidentally included
    (rm -rf var || true) \
    # Create the var sub-directories
    && mkdir -p var/cache var/logs var/sessions \
    # Install dependencies
    && composer install --optimize-autoloader --no-scripts \
    # Fixes permissions issues in non-dev mode
    && chown -R www-data . var/cache var/logs var/sessions

CMD ["/app/docker/start.sh"]
