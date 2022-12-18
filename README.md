# YesTicket Custom

Custom Version of [YesTicket's Wordpress Plugin](https://www.yesticket.org/login/de/integration.php#wp-plugin).

## Why?

* Need event images, when using the event_shortcode
* Need to filter events by their name (done via str_contains())
* Need proper styling when displaying a single event (when count="1")

## Installation

Zip all files inside the `yesticket` folder. (The resulting ZIP should contain the files directly)

1. In your Wordpress go to `Plugins > Install`.
2. Select the freshly packed ZIP archive.
3. Activate plugin
4. Configure the necessary settings
5. Use the shortcodes...

# Translations

[Official Wordpress Guide](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)

['Loco' Plugin to help translating locally](https://wordpress.org/plugins/loco-translate/)

# Knowledge

## API

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
  },
  ...
]
```

# Development Environment

Credits to https://github.com/nezhar/wordpress-docker-compose

Setup will run on [http://127.0.0.1](http://127.0.0.1).

## Run

    docker-compose up -d

*Note that wp-cli will crash until you finish the WP installation*
*Might need to add '--force-recreate'*

## Stop

    docker-compose stop

## Testing

Credits to https://github.com/chriszarate/docker-compose-wordpress

### Setup Tests

Make sure the scripts in `bin` have `LF` line-endings!

    docker-compose -f docker-compose.yml -f docker-compose.phpunit.yml up -d
    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit /mount/bin/install-wp-tests.sh wordpress_test root test mysql_phpunit latest true

### Running Tests

    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit phpunit [args...]
    docker-compose -f docker-compose.phpunit.yml run --rm wordpress_phpunit phpunit tests

### Stop Test Container

    docker-compose -f docker-compose.yml -f docker-compose.phpunit.yml stop
