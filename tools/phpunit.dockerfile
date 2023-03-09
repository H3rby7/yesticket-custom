ARG VERSION_PHP=7.2
ARG VERSION_PHP_UNIT=8
# https://hub.docker.com/r/wordpressdevelop/phpunit/tags
FROM wordpressdevelop/phpunit:$VERSION_PHP_UNIT-php-$VERSION_PHP-fpm
RUN apt-get update && \
    apt-get install -y mariadb-client wget curl subversion
ARG VERSION_XDEBUG=3.1.6
RUN wget https://xdebug.org/files/xdebug-$VERSION_XDEBUG.tgz && \
    tar -xzf xdebug-$VERSION_XDEBUG.tgz && \
    cd xdebug-$VERSION_XDEBUG && \
    phpize && \
    ./configure --enable-xdebug && \
    make && \
    make install && \
    cd .. && \
    echo zend_extension=xdebug > /usr/local/etc/php/conf.d/99-xdebug.ini


ENTRYPOINT []

CMD /bin/true