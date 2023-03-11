#!/usr/bin/env bash

ADMIN_USER="${WP_USER:-yt}"
ADMIN_PW="${WP_USER_PW:-yt}"
SETTINGS_ORGANIZER_ID="${YESTICKET_ORGANIZER_ID}"
ORGANIZER_LENGTH=${#SETTINGS_ORGANIZER_ID}
SETTINGS_KEY="${YESTICKET_KEY}"
KEY_LENGTH=${#SETTINGS_KEY}

WP_CLI_OPTS=$1
WP_MENU_NAME="main-menu"

function setupWP() {
  # Install WordPress.
  wp core install \
    --title="YesTicket Development" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PW" \
    --admin_email="admin@example.com" \
    --url="http://127.0.0.1" \
    --skip-email \
    $WP_CLI_OPTS

  # Update permalink structure.
  wp option update permalink_structure "/%year%/%monthnum%/%postname%/" --skip-themes --skip-plugins $WP_CLI_OPTS
}

function configurePlugins() {
  # Install Loco-Translate for translations
  wp plugin install loco-translate $WP_CLI_OPTS
  wp plugin activate loco-translate $WP_CLI_OPTS

  # Activate Yesticket plugin.
  wp plugin activate yesticket $WP_CLI_OPTS
  wp option set yesticket_settings_required "{\"organizer_id\": \"$SETTINGS_ORGANIZER_ID\", \"api_key\": \"$SETTINGS_KEY\"}" --format=json $WP_CLI_OPTS
  wp cache flush $WP_CLI_OPTS
}

function createMenu() {
  wp menu create "$WP_MENU_NAME" $WP_CLI_OPTS
  wp menu location assign "$WP_MENU_NAME" primary $WP_CLI_OPTS
}

function examplePage() {
  name=$1
  title=$2
  content=$3
  echo "Creating '$title' page with '$content'" >&2
  # Success: Created post 13.
  postId=`wp post create --post_author=1 \
                 --post_type=page \
                 --post_status=publish \
                 --post_name="$name" \
                 --post_title="$title" \
                 --post_content="$content" \
                 | sed -r 's/Success: Created post (\d+)./\1/g'`
  if [[ $postId > 0 ]]
  then
    echo "Created page [$postId] and will add to menu [$WP_MENU_NAME]" >&2
    wp menu item add-post "$WP_MENU_NAME" $postId --title="$title" $WP_CLI_OPTS
  else
    echo "Error creating page [$name]"
  fi
}

function createExampleShortCodePageEvents() {
  if [[ `wp post --post_type=page list | grep ytp_events -w | wc -l` > 0 ]]
  then
    echo "example page for [yesticket_events] already exists" >&2
    return
  fi
  echo "Creating example page for [yesticket_events]" >&2
  read -r -d '' CONTENT << EOM
    <!-- wp:paragraph -->
    <h1>light theme</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_events env="dev" type="all" count="5" details="yes" theme="light"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <h1>dark theme</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_events env="dev" type="all" count="5" details="yes" theme="dark"]
    <!-- /wp:shortcode -->
EOM
  examplePage 'ytp_events' 'Events' "$CONTENT"
}

function createExampleShortCodePageEventsList() {
  if [[ `wp post --post_type=page list | grep ytp_events_list -w | wc -l` > 0 ]]
  then
    echo "example page for [yesticket_events_list] already exists" >&2
    return
  fi
  echo "Creating example page for [yesticket_events_list]" >&2
  read -r -d '' CONTENT << EOM
    <!-- wp:shortcode -->
    [yesticket_events_list env="dev" type="all"]
    <!-- /wp:shortcode -->
EOM
  examplePage 'ytp_events_list' 'Events List' "$CONTENT"
}

function createExampleShortCodePageEventsCards() {
  if [[ `wp post --post_type=page list | grep ytp_events_cards -w | wc -l` > 0 ]]
  then
    echo "example page for [yesticket_events_cards] already exists" >&2
    return
  fi
  echo "Creating example page for [yesticket_events_cards]" >&2
  read -r -d '' CONTENT << EOM
    <!-- wp:paragraph -->
    <h1>light theme</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_events_cards env="dev" type="all" details="no" count="6" theme="light"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <h1>dark theme</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_events_cards env="dev" type="all" details="no" count="6" theme="dark"]
    <!-- /wp:shortcode -->
EOM
  examplePage 'ytp_events_cards' 'Events Cards' "$CONTENT"
}

function createExampleShortCodePageTestimonials() {
  if [[ `wp post --post_type=page list | grep ytp_testimonials -w | wc -l` > 0 ]]
  then
    echo "example page for [yesticket_testimonials] already exists" >&2
    return
  fi
  echo "Creating example page for [yesticket_testimonials]" >&2
  read -r -d '' CONTENT << EOM
    <!-- wp:paragraph -->
    <h1>design basic</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_testimonials env="dev" details="yes" count="10"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <h1>design jump</h1>
    <!-- /wp:paragraph -->
    <!-- wp:shortcode -->
    [yesticket_testimonials env="dev" details="yes" count="10" design="jump"]
    <!-- /wp:shortcode -->
EOM
  examplePage 'ytp_testimonials' 'Testimonials' "$CONTENT"
}

function createExampleShortCodePageEventSlides() {
  if [[ `wp post --post_type=page list | grep ytp_slides -w | wc -l` > 0 ]]
  then
    echo "example page for [yesticket_slides] already exists" >&2
    return
  fi
  echo "Creating example page for [yesticket_slides]" >&2
  read -r -d '' CONTENT << EOM
    <!-- wp:shortcode -->
    [yesticket_slides env="dev" ms-per-slide="30000" color-1="#ddd" color-2="#333" welcome-1="erste Zeile" welcome-2="zweite Zeile" welcome-3="drei (zeigt eine vier)" teaser-length="200" text-scale="120%"]
    <!-- /wp:shortcode -->
EOM
  examplePage 'ytp_slides' 'Slides' "$CONTENT"
}

while ! mysqladmin ping -h"$WORDPRESS_DB_HOST" --silent; do
  echo "Waiting for DB to be ready..."
  sleep 1
done

setupWP
createMenu
configurePlugins

createExampleShortCodePageEvents
createExampleShortCodePageEventsList
createExampleShortCodePageEventsCards
createExampleShortCodePageTestimonials
createExampleShortCodePageEventSlides