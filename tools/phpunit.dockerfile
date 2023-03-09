ARG VERSION_PHP=7.2
ARG VERSION_PHP_UNIT=8
# https://hub.docker.com/r/wordpressdevelop/phpunit/tags
FROM wordpressdevelop/phpunit:$VERSION_PHP_UNIT-php-$VERSION_PHP-fpm
ARG VERSION_WP=6.1.1
ARG VERSION_XDEBUG=3.1.6
# For MacOS-X SED set to '-i .bak'
ARG SED_OPTION=-i
RUN apt-get update && \
    apt-get install -y mariadb-client wget curl subversion
RUN wget https://xdebug.org/files/xdebug-$VERSION_XDEBUG.tgz && \
    tar -xzf xdebug-$VERSION_XDEBUG.tgz && \
    cd xdebug-$VERSION_XDEBUG && \
    phpize && \
    ./configure --enable-xdebug && \
    make && \
    make install && \
    cd .. && \
    echo zend_extension=xdebug > /usr/local/etc/php/conf.d/99-xdebug.ini
COPY yesticket/composer.* .
RUN composer update -n
RUN mkdir -p /tmp/wordpress-tests-lib
RUN svn co --quiet https://develop.svn.wordpress.org/tags/$VERSION_WP/tests/phpunit/includes/ /tmp/wordpress-tests-lib/includes && \
    svn co --quiet https://develop.svn.wordpress.org/tags/$VERSION_WP/tests/phpunit/data/ /tmp/wordpress-tests-lib/data && \
    curl -s https://develop.svn.wordpress.org/tags/$VERSION_WP/wp-tests-config-sample.php > /tmp/wordpress-tests-lib/wp-tests-config.php
RUN sed $SED_OPTION "s:dirname( __FILE__ ) . '/src/':'/tmp/vendor/johnpbloch/wordpress-core/':" /tmp/wordpress-tests-lib/wp-tests-config.php && \
    sed $SED_OPTION "s/youremptytestdbnamehere/wordpress_test/" /tmp/wordpress-tests-lib/wp-tests-config.php && \
    sed $SED_OPTION "s/yourusernamehere/root/" /tmp/wordpress-tests-lib/wp-tests-config.php && \
    sed $SED_OPTION "s/yourpasswordhere/test/" /tmp/wordpress-tests-lib/wp-tests-config.php && \
    sed $SED_OPTION "s|localhost|mysql_phpunit|" /tmp/wordpress-tests-lib/wp-tests-config.php

ENTRYPOINT []

CMD /bin/true