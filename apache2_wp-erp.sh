#!/bin/bash

if [[ ! $(sudo -u www-data wp core is-installed) ]]
then
    sudo -u www-data wp core install --url=${SQUASH_DOMAIN} --title="squash.io test site" --admin_name=${WORDPRESS_ADMIN_USER} --admin_password=${WORDPRESS_ADMIN_PASSWORD} --admin_email=test@example.com
    sudo -u www-data wp plugin activate wp-erp
fi
apache2-foreground
