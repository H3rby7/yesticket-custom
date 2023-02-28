<?php

namespace YesTicket\Model;

class Event
{

  static public function fromJson($item)
  {
    $Obj = new Event();
    $prop = \get_object_vars($item);
    foreach ($prop as $key => $lock) {
      if (\property_exists($Obj,  $key)) {
        $Obj->$key = $item->$key;
      }
    }
    return $Obj;
  }

  /**
   * @var string
   */
  public $event_name;

  /**
   * @var string
   */
  public $event_type;

  /**
   * @var string
   */
  public $event_id;

  /**
   * @var string
   */
  public $event_datetime;

  /**
   * @var string
   */
  public $event_datetime_end;

  /**
   * @var string
   */
  public $event_description;

  /**
   * @var string
   */
  public $event_urlsafename;

  /**
   * @var string
   */
  public $event_picture_url;

  /**
   * @var string
   */
  public $event_max_people;

  /**
   * @var string
   */
  public $event_free_seats;

  /**
   * @var string
   */
  public $event_blocked_seats;

  /**
   * @var string
   */
  public $event_days_to_event;

  /**
   * @var string
   */
  public $event_urgency_string;

  /**
   * @var string
   */
  public $event_bookable_from;

  /**
   * @var string
   */
  public $event_bookable_to;

  /**
   * @var string
   */
  public $event_facebook_url;

  /**
   * @var string
   */
  public $event_payment_mode;

  /**
   * @var string
   */
  public $event_notes_help;

  /**
   * @var string
   */
  public $event_external_booking_url;

  /**
   * @var string
   */
  public $location_name;

  /**
   * @var string
   */
  public $location_description;

  /**
   * @var string
   */
  public $location_help_notes;

  /**
   * @var string
   */
  public $location_street;

  /**
   * @var string
   */
  public $location_city;

  /**
   * @var string
   */
  public $location_zip;

  /**
   * @var string
   */
  public $location_state;

  /**
   * @var string
   */
  public $location_country;

  /**
   * @var string
   */
  public $organizer_name;

  /**
   * @var string
   */
  public $organizer_language;

  /**
   * @var string
   */
  public $yesticket_booking_url;

  /**
   * @var string
   */
  public $tickets;

  public function getPictureUrl()
  {
    if (!empty($this->event_id)) {
      return "/wp-json/yesticket/v1/picture/" . $this->event_id;
    }
    // Fallback
    $query = \parse_url($this->event_picture_url, \PHP_URL_QUERY);
    preg_match('/event=(<id>\d+)/', $query, $matches);
    return "/wp-json/yesticket/v1/picture/" . $matches['id'];
  }
}
