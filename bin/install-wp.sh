#!/usr/bin/env sh

# Install WordPress.
wp core install \
  --title="YesTicket Development" \
  --admin_user="yesticket" \
  --admin_password="yesticket" \
  --admin_email="admin@example.com" \
  --url="http://project.test" \
  --skip-email

# Update permalink structure.
wp option update permalink_structure "/%year%/%monthnum%/%postname%/" --skip-themes --skip-plugins

# Activate plugin.
wp plugin activate yesticket
