<?php

namespace YesTicket\Model;

use YesTicket\Model\Event;

class EventTest extends \WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Model\Event"));
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_fromStdClass()
  {
    $input = \json_decode($this->getInputEventJson());
    $result = Event::fromStdClass($input);
    $this->assertSame('Event #42, Best Show Everrr', $result->event_name);
    $this->assertSame('Auftritt', $result->event_type);
    $this->assertSame('1234', $result->event_id);
    $this->assertSame('2022-03-27 20:00:00', $result->event_datetime);
    $this->assertSame('2022-03-27 22:00:00', $result->event_datetime_end);
    $this->assertSame('My description of this amazing event - super awesome btw. tbh. so this might be a few lines long yeah', $result->event_description);
    $this->assertSame('my-event-27-03-22', $result->event_urlsafename);
    $this->assertSame('https://www.yesticket.org/picture.php?event=1234', $result->event_picture_url);
    $this->assertSame('50', $result->event_max_people);
    $this->assertSame('33', $result->event_free_seats);
    $this->assertSame('17', $result->event_blocked_seats);
    $this->assertSame('26', $result->event_days_to_event);
    $this->assertSame('Tickets verfügbar, noch 26 Tage', $result->event_urgency_string);
    $this->assertSame('2021-11-30 00:00:00', $result->event_bookable_from);
    $this->assertSame('2022-03-27 18:00:00', $result->event_bookable_to);
    $this->assertSame('https://fb.me/e/1234test', $result->event_facebook_url);
    $this->assertSame('payment_unwanted', $result->event_payment_mode);
    $this->assertSame('some help text', $result->event_notes_help);
    $this->assertSame('https://some.url', $result->event_external_booking_url);
    $this->assertSame('Not the batcave', $result->location_name);
    $this->assertSame('A cave under a mansion of a rich guy', $result->location_description);
    $this->assertSame("How-to-get-there long text. Don't take a car. Cars are bad. Come by bike!", $result->location_help_notes);
    $this->assertSame('Main Str. 69', $result->location_street);
    $this->assertSame('Gotham', $result->location_city);
    $this->assertSame('12345', $result->location_zip);
    $this->assertSame('Madeup State', $result->location_state);
    $this->assertSame('A Country', $result->location_country);
    $this->assertSame('Batm', $result->organizer_name);
    $this->assertSame('de', $result->organizer_language);
    $this->assertSame('https://www.yesticket.org/event/de/my-event-27-03-22', $result->yesticket_booking_url);
    $this->assertSame('Zahle nach der Show so viel es dir Wert war (AK: 0,00 EUR/VVK: 0,00 EUR)', $result->tickets);
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_fromStdClass_input_nulls()
  {
    $input = \json_decode('{}');
    $result = Event::fromStdClass($input);
    $this->assertNull($result->event_name);
    $this->assertNull($result->event_type);
    $this->assertNull($result->event_id);
    $this->assertNull($result->event_datetime);
    $this->assertNull($result->event_datetime_end);
    $this->assertNull($result->event_description);
    $this->assertNull($result->event_urlsafename);
    $this->assertNull($result->event_picture_url);
    $this->assertNull($result->event_max_people);
    $this->assertNull($result->event_free_seats);
    $this->assertNull($result->event_blocked_seats);
    $this->assertNull($result->event_days_to_event);
    $this->assertNull($result->event_urgency_string);
    $this->assertNull($result->event_bookable_from);
    $this->assertNull($result->event_bookable_to);
    $this->assertNull($result->event_facebook_url);
    $this->assertNull($result->event_payment_mode);
    $this->assertNull($result->event_notes_help);
    $this->assertNull($result->event_external_booking_url);
    $this->assertNull($result->location_name);
    $this->assertNull($result->location_description);
    $this->assertNull($result->location_help_notes);
    $this->assertNull($result->location_street);
    $this->assertNull($result->location_city);
    $this->assertNull($result->location_zip);
    $this->assertNull($result->location_state);
    $this->assertNull($result->location_country);
    $this->assertNull($result->organizer_name);
    $this->assertNull($result->organizer_language);
    $this->assertNull($result->yesticket_booking_url);
    $this->assertNull($result->tickets);
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_event_id_present()
  {
    $input = new Event(true);
    $input->event_id = 1;
    $this->assertSame('/wp-json/yesticket/v1/picture/1', $input->getPictureUrl());
  }
  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_take_from_event_picture_url()
  {
    $input = new Event(true);
    $input->event_picture_url = 'https://www.yesticket.org/picture.php?event=69';
    $this->assertSame('/wp-json/yesticket/v1/picture/69', $input->getPictureUrl());
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_null()
  {
    $input = new Event(true);
    $this->assertSame("https://www.yesticket.org/dev/picture.php?event=0", $input->getPictureUrl());
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_malformed()
  {
    $input = new Event(true);
    $input->event_picture_url = "not an url";
    $this->assertSame("https://www.yesticket.org/dev/picture.php?event=0", $input->getPictureUrl());
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_no_event_id_given()
  {
    $input = new Event(true);
    $input->event_picture_url = "https://www.yesticket.org/dev/picture.php?not=given";
    $this->assertSame("https://www.yesticket.org/dev/picture.php?not=given", $input->getPictureUrl());
  }

  /**
   * @covers YesTicket\Model\Event
   */
  function test_getPictureUrl_no_fopen_allowed()
  {
    $input = new Event(false);
    $input->event_id = 1;
    $input->event_picture_url = 'https://www.yesticket.org/picture.php?event=69';
    $result =  $input->getPictureUrl();
    $this->assertSame('https://www.yesticket.org/picture.php?event=69', $result);
  }

  private function getInputEventJson() {
    return <<<EOD
    {
      "event_name":"Event #42, Best Show Everrr",
      "event_type":"Auftritt",
      "event_id":"1234",
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
      "event_notes_help":"some help text",
      "event_external_booking_url":"https:\/\/some.url",
      "location_name":"Not the batcave",
      "location_description":"A cave under a mansion of a rich guy",
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
    }
EOD;
  }

}
