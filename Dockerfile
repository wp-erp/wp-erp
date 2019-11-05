FROM wordpress

COPY . /usr/src/wordpress/wp-content/plugins/wp-erp

RUN apt-get update && apt-get install -y unzip git sudo && \
    cd /usr/src/wordpress/wp-content/plugins/ && \
    sudo chown -R www-data wp-erp && \
    cd /usr/src/wordpress/wp-content/plugins/wp-erp && \
    curl --output composer-setup.php https://getcomposer.org/installer && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm -f composer-setup.php && \
    mkdir /var/www/.composer && chown www-data:www-data /var/www/.composer && \
    sudo -u www-data composer install && \
    sudo -u www-data composer dump-autoload -o && \
    curl --output /usr/local/sbin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x /usr/local/sbin/wp

COPY apache2_wp-erp.sh /usr/local/bin/apache2_wp-erp.sh
CMD ["apache2_wp-erp.sh"]
