<?php

namespace YesTicket\Model;

class Event
{
  /**
   * @var string
   */
  private const YESTICKET_FALLBACK_IMAGE_URL = "https://www.yesticket.org/dev/picture.php?event=0";

  /**
   * 'Convert' PHP StdClass into Event obj.
   * E.G. to use after \json_decode()
   * 
   * @param object $item encoded appropriate PHP type
   * @return Event
   */
  static public function fromStdClass($item)
  {
    // Cach implementation relies on the php.ini param below
    $can_cache = \filter_var(\ini_get('allow_url_fopen'), \FILTER_VALIDATE_BOOLEAN);
    $Obj = new Event($can_cache);
    $prop = \get_object_vars($item);
    foreach ($prop as $key => $lock) {
      if (\property_exists($Obj,  $key)) {
        $Obj->$key = $item->$key;
      }
    }
    return $Obj;
  }

  public function __construct($use_cache)
  {
    $this->use_cache = $use_cache;
  }

  /**
   * @var boolean
   */
  private $use_cache;

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

  /**
   * Get URL of event picture redirected to our ImageAPI, so we can cache it.
   * @return string the URL
   */
  public function getPictureUrl()
  {
    if (!$this->use_cache) {
      return $this->event_picture_url;
    }
    if (!empty($this->event_id)) {
      return $this->pictureUrlFromId($this->event_id);
    }
    if (empty($this->event_picture_url)) {
      // Does not have an own image?!
      return Event::YESTICKET_FALLBACK_IMAGE_URL;
    }
    // Fallback, extract ID from 'event_picture_url' if possible
    $query = \parse_url($this->event_picture_url, \PHP_URL_QUERY);
    if (!$query) {
      return Event::YESTICKET_FALLBACK_IMAGE_URL;
    }
    \preg_match('/event=(?<id>\d+)/', $query, $matches);
    if (\array_key_exists('id', $matches)) {
      return $this->pictureUrlFromId($matches['id']);
    }
    return $this->event_picture_url;
  }

  private function pictureUrlFromId($id) {
    return \get_site_url(null, "wp-json/yesticket/v1/picture/$id");
  }
}
