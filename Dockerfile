ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-cli

ARG COMPOSER_FLAGS="--prefer-dist --no-interaction"
ARG SYMFONY_REQUIRE=5.*

ARG DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_PROCESS_TIMEOUT 3600

WORKDIR /code/

COPY docker/php-prod.ini /usr/local/etc/php/php.ini
COPY docker/composer-install.sh /tmp/composer-install.sh

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
	&& rm -r /var/lib/apt/lists/* \
	&& chmod +x /tmp/composer-install.sh \
	&& /tmp/composer-install.sh

# To enable SYMFONY_REQUIRE
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer \
 && composer global config allow-plugins.symfony/flex true \
 && composer global require --no-progress --no-scripts --no-plugins symfony/flex

## Composer - deps always cached unless changed
# First copy only composer files
COPY composer.* /code/
# Download dependencies, but don't run scripts or init autoloaders as the app is missing
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader
# copy rest of the app
COPY . /code/
# run normal composer - all deps are cached already
RUN composer install $COMPOSER_FLAGS

CMD ["php", "/code/src/run.php"]
