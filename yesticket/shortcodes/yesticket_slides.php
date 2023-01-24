<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_slides', 'getYesTicketSlides');
add_action('wp_enqueue_scripts', 'webslides_styles');

function webslides_styles()
{
  wp_enqueue_style('yesticket_slides_base', plugins_url('webslides/webslides.css', __FILE__), false, 'all');
  wp_enqueue_style('yesticket_slides_custom', plugins_url('webslides/yesticket.css', __FILE__), false, 'all');
  wp_enqueue_script('yesticket_slides', plugins_url('webslides/webslides.min.js', __FILE__));
}

function getYesTicketSlides($atts)
{
    $att = shortcode_atts(array(
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '100',
                    'max-text-length' => '250',
                    'theme' => 'light',
                    ), $atts);
    $content = "";
    try {
        $result = getEventsFromApi($att);
        if ($att["theme"] == "light") {
            $content .= "<div class='yt-light'>";
        } elseif ($att["theme"] == "dark") {
            $content .= "<div class='yt-dark'>";
        } else {
            $content .= "<div class='yt-default ".$att["theme"]."'>";
        }
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= render_yesTicketSlides($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketSlides($result, $att) {
    ?>
    <main role="main">
      <article id="webslides">

        <!-- Quick Guide
          - Each parent <section> in the <article id="webslides"> element is an individual slide.
          - Vertical sliding = <article id="webslides" class="vertical">
          - <div class="wrap"> = container 90% / <div class="wrap size-50"> = 45%;
        -->

        <!-- Just 5 basic animations: .fadeIn, .fadeInUp, .zoomIn, .slideInLeft, and .slideInRight. -->

        <section class="">
         <span class="background dark"></span>
          <div class="wrap aligncenter slow">
            <p class="text-symbols">Wilkommen zu</p>
            <h1 class="text-landing">Kanonenfutter</h1>
            <p class="text-symbols">das improtheater</p>
          </div>
        </section>
        <?php 
        foreach ($result as $item):
          echo render_yesTicketEventSlide($item, $att);
        endforeach;
        ?>
      </article>
    </main>
    <!--main-->

    <script>
      window.addEventListener('load', function () {
        window.ws = new WebSlides(
        //  { autoslide: 10000 }
        );
      }, false);
    </script>
    <?php
}

function render_yesTicketEventSlide($event, $att) {
  ?>
  <section class="yesticket-slide">
    <span class="background dark fadeIn" style="background-image:url('<?php echo $event->event_picture_url; ?>')"></span>
      <div class="yesticket-event-meta slide-top slideInLeft delay1 bg-trans-dark">
          <h2 class="yesticket-event-name"><?php echo $event->event_name; ?></h2>
          <p><?php echo ytp_render_date_and_time($event->event_datetime) . ", " . $event->location_name; ?></p>
      </div>
      <div class="yesticket-event-teaser slideInRight delay2">
        <p class="bg-trans-dark">
          <?php echo render_yesTicketEventDescriptionForSlides($event, $att); ?>
        </p>
      </div>
      <!-- end .yesticket-slide-->
  </section>
<?php }

function render_yesTicketEventDescriptionForSlides($item, $att) {
  $shorter = substr($item->event_description, 0, $att["max-text-length"]);
  $indexOfLastPeriod = strrpos($shorter, ".");
  if (!$indexOfLastPeriod) {
    return $shorter . "...";
  } else {
    return substr($shorter, 0, $indexOfLastPeriod + 1);
  }
}

function render_yesTicketSlidesHelp() {?>
    <h2><?php echo __("Shortcodes for your events as slides.", "yesticket");?></h2>
    <?php
}
?>