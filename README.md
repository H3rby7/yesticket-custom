# YesTicket Custom

Custom Version of [YesTicket's Wordpress Plugin](https://www.yesticket.org/login/de/integration.php#wp-plugin).

## Why?

Need event image in event_shortcode.

## Installation

Zip all files inside the `yesticket-custom` folder. (The resulting ZIP should contain the files directly)

Then follow along with the original's install instructions, just use our own freshly packed ZIP as basis.

Eg:
1. In your Wordpress go to `Plugins > Install`.
2. Select the freshly packed ZIP archive.
3. Activate plugin
4. Use the shortcodes...

# Knowledge

## API

Get your organizer ID and Key on the [YesTicket Integration Page](https://www.yesticket.org/login/de/integration.php).

Use organizerr ID and Key instead of `<ORGID>` and `<KEY>` in the listed requests.

### GET Events 

URL: https://www.yesticket.org/api/events-endpoint.php?organizer=<ORGID>&type=all&key=<KEY>

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