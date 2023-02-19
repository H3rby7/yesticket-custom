ARG VERSION_PHP=7.2
ARG VERSION_PHP_UNIT=8
# https://hub.docker.com/r/wordpressdevelop/phpunit/tags
FROM wordpressdevelop/phpunit:$VERSION_PHP_UNIT-php-$VERSION_PHP-fpm

RUN apt-get install -y mariadb-client wget curl subversion

ENTRYPOINT []

CMD /bin/true