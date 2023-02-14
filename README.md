# YesTicket WP Plugin

Wordpress Plugin to display Events, Shows, Performances, Workshops, Festivals and Audience Feedback (Testimonials) from the ["YesTicket" Online Ticketing Service](https://www.yesticket.org/).

# Installing in Wordpress

1. In your Wordpress go to `Plugins > Install`.
2. Select the plugin ZIP archive.
3. Activate plugin
4. Configure the necessary settings on the plugin page
    * You need an account at https://www.yesticket.org/.
5. Use the shortcodes. For help visit the plugin settings page.

## Customizing

You can find the client-side CSS [here](./yesticket/front.css). Use your own CSS to overwrite the necessary styles.

# Developing

[DEV-Setup Credits to nezhar](https://github.com/nezhar/wordpress-docker-compose)

Setup will run on [http://127.0.0.1](http://127.0.0.1).

## Run DEV ENV

    docker-compose up -d

*Note that wp-cli will crash until you finish the WP installation*
*Might need to add '--force-recreate'*

## Stop DEV ENV

    docker-compose stop

## Deployment / Shipping

Update [this file](README.md) and also update [the changelog](CHANGELOG.md)!

### Automated

Requires docker. Windows users: Make sure the `bin/*.sh` files have LF file endings.

    docker-compose -f docker-compose.zip.yml run --rm zipper

### Manual

Zip the files inside the `yesticket` folder. It should contain the following files and folders:

    |_admin.css
    |_front.css
    |_front.js
    |_yesticket_api.php
    |_yesticket_helpers.php
    |_yesticket_plugin_page.php
    |_yesticket.php
    |_admin/*
        |_(whole folder)
    |_img/*
        |_(whole folder)
    |_languages/*
        |_(whole folder)
    |_shortcodes/*
        |_(whole folder)
## Manual Testing

### Manual Testing Setup

Describes how the manual testing is being done.

1. Start the [DEV setup](#run-dev-env)
2. Finish WP Installation
3. Configure YesTicket Plugin
4. Create a page as shown below

<details>
    <summary>Create a page with this content</summary>

    <!-- wp:paragraph -->
    <p>yesticket_events</p>
    <!-- /wp:paragraph -->

    <!-- wp:shortcode -->
    [yesticket_events env="dev" type="all"  count="5" details="yes" theme="light"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <p>yesticket_testimonials</p>
    <!-- /wp:paragraph -->

    <!-- wp:shortcode -->
    [yesticket_testimonials env="dev" details="yes" count="10"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <p>yesticket_events_cards</p>
    <!-- /wp:paragraph -->

    <!-- wp:shortcode -->
    [yesticket_events_cards env="dev" type="all" details="no" count="6" theme="light"]
    <!-- /wp:shortcode -->

    <!-- wp:paragraph -->
    <p>yesticket_events_list</p>
    <!-- /wp:paragraph -->

    <!-- wp:shortcode -->
    [yesticket_events_list env="dev" type="all"]
    <!-- /wp:shortcode -->

</details>

5. Create another page to check the slide-show as shown below

<details>
    <summary>Create a page with this content</summary>

    <!-- wp:shortcode -->
    [yesticket_slides env="dev" ms-per-slide="30000" color-1="#ddd" color-2="#333" welcome-1="erste Zeile" welcome-2="zweite Zeile" welcome-3="drei (zeigt eine vier)" teaser-length="200" text-scale="120%"]
    <!-- /wp:shortcode -->

</details>

### Manual Testing Process

1. On Settings page trigger all functions once
    * Save
    * Clear Cache
2. On Settings page go through all TABs
3. On created content page check if all shortcodes render correctly
    * remember to 'display details', where possible
4. Switch to a different image in [docker-compose](docker-compose.yml) and repeat the testing process.

## Automated Test-Setup

TODO: 
- [ ] write and use automated tests.
- [ ] run automated tests for different PHP/WP versions

[Credits to chriszarate](https://github.com/chriszarate/docker-compose-wordpress)

For a list of common assertions [see this](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#using-assertions).

### Setup automated Tests

Make sure the scripts in `bin` have `LF` line-endings!

    docker-compose -f docker-compose.yml -f docker-compose.phpunit.yml up -d --build
    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit /app/bin/install-wp-tests.sh wordpress_test root test mysql_phpunit latest true

### Running automated Tests

    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit phpunit --debug [args...]
    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit phpunit --debug

### Stop automated Test Container

    docker-compose -f docker-compose.yml -f docker-compose.phpunit.yml stop

Then [install as instructed above](#installing-in-wordpress).
# Translations

Followed the [Official Wordpress Guide](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/).

If you want to add a translation you can use the ['Loco' Plugin to help translating locally](https://wordpress.org/plugins/loco-translate/).

***Note that some text is generated by the YesTicket API***

## Knowledge

Collection of noticable details...

### API

Get your organizer ID and Key on the [YesTicket Integration Page](https://www.yesticket.org/login/de/integration.php).

Use organizerr ID and Key instead of `<ORGID>` and `<KEY>` in the listed requests.

### GET Events 

URL: https://www.yesticket.org/api/v2/events.php?organizer=<ORGID>&type=all&key=<KEY>

```json
[
  {
    "event_name":"Event #42, Best Show Everrr",
    "event_type":"Auftritt",
    "event_datetime":"2022-03-27 20:00:00",
    "event_datetime_end":"2022-03-27 22:00:00",
    "event_description":"My description of this amazing event - super awesome btw. tbh. so this might be a few lines long yeah",
    "event_urlsafename":"my-event-27-03-22",
    "event_picture_url":"https:\/\/www.yesticket.org\/picture.php?event=1234",
    "event_max_people":"50",
    "event_free_seats":"33",
    "event_blocked_seats":"17",
    "event_days_to_event":"26",
    "event_urgency_string":"Tickets verf\u00fcgbar, noch 26 Tage",
    "event_bookable_from":"2021-11-30 00:00:00",
    "event_bookable_to":"2022-03-27 18:00:00",
    "event_facebook_url":"https:\/\/fb.me\/e\/1234test",
    "event_payment_mode":"payment_unwanted",
    "event_notes_help":"",
    "event_external_booking_url":"",
    "location_name":"Not the batcave",
    "location_description":"",
    "location_help_notes":"How-to-get-there long text. Don't take a car. Cars are bad. Come by bike!",
    "location_street":"Main Str. 69",
    "location_city":"Gotham",
    "location_zip":"12345",
    "location_state":"Madeup State",
    "location_country":"A Country",
    "organizer_name":"Batm",
    "organizer_language":"de",
    "yesticket_booking_url":"https:\/\/www.yesticket.org\/event\/de\/my-event-27-03-22",
    "tickets":"Zahle nach der Show so viel es dir Wert war (AK: 0,00 EUR\/VVK: 0,00 EUR)"
    ...
  },
  ...
]
```
