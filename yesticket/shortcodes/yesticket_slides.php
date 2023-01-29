<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_slides', 'getYesTicketSlides');
add_action('wp_enqueue_scripts', 'webslides_styles');

function webslides_styles()
{
  wp_enqueue_style('yesticket_slides', plugins_url('webslides/webslides.css', __FILE__), false, 'all');
  wp_enqueue_script('yesticket_slides', plugins_url('webslides/webslides.min.js', __FILE__));
}

function getYesTicketSlides($atts)
{
    $att = shortcode_atts(array(
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '10',
                    'teaser-length' => '250',
                    'ms-per-slide' => '10000',
                    'text-scale' => '100%',
                    'color-1' => '#ffffff',
                    'color-2' => '#000000',
                    'welcome-1' => __('welcome to our', "yesticket"),
                    'welcome-2' => __('improv theatre show', "yesticket"),
                    'welcome-3' => __('where everything is made up', "yesticket"),
                    ), $atts);
    $content = "";
    try {
        $result = ytp_api_getEvents($att);
        $content .= render_yesTicketSlideInlineStyles($att);
        $content .= "<div id='ytp-slides' style='font-size: ".$att["text-scale"]."'>";
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

function render_yesTicketSlideInlineStyles($att) {
  $color1 = $att['color-1'];
  $color2 = $att['color-2'];
  return <<<EOD
  <style>
    #ytp-slides {
      --ytp--color--primary: $color1;
      --ytp--color--contrast: $color2;
    }
  </style>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function render_yesTicketSlides($result, $att) {
  $content = <<<EOD
  <main role="main">
    <article id="webslides">
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  $content .= render_yesTicketWelcomeSlide($att["welcome-1"], $att["welcome-2"], $att["welcome-3"]);
  $count = 0;
  foreach ($result as $item) {
    $content .= render_yesTicketEventSlide($item, $att);
    $count++;
    if ($count == (int)$att["count"]) {
        break;
    }
  }
  $content .= <<<EOD
    </article>
  </main>
  <!--main-->
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  $autoslide = $att["ms-per-slide"];
  $content .= render_yesTicketWebslidesJS($autoslide);
  return $content;
}

function render_yesTicketWelcomeSlide($row1, $row2, $row3) {
return <<<EOD
      <section class="">
        <div class="wrap aligncenter slow">
          <p class="text-symbols">$row1</p>
          <h1 class="text-landing">$row2</h1>
          <p class="text-symbols">$row3</p>
        </div>
      </section>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function render_yesTicketWebslidesJS($autoslide) {
return <<<EOD
<script>
window.addEventListener('load', function () {
  window.ws = new WebSlides(
    { autoslide: $autoslide }
  );
}, false);
</script>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function render_yesTicketEventSlide($event, $att) {
  $bg_image_url = $event->event_picture_url;
  $event_name = $event->event_name;
  $date_and_location = ytp_render_date_and_time($event->event_datetime) . ", " . $event->location_name;
  $description = render_yesTicketEventDescriptionForSlides($event, $att);
  return <<<EOD
  <section class="yesticket-slide">
    <span class="background fadeIn" style="background-image:url('$bg_image_url')"></span>
    <div class="wrap">
      <div class="yesticket-event-meta slide-top slideInLeft delay">
        <h2 class="yesticket-event-name">$event_name</h2>
        <p>$date_and_location</p>
        <div class="backdrop-dark"><div></div></div>
      </div>
      <div class="yesticket-event-teaser slideInRight delay delay2">
        <p>$description</p>
        <div class="backdrop-dark"><div></div></div>
      </div>
    </div>
    <!-- end .yesticket-slide-->
  </section>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function render_yesTicketEventDescriptionForSlides($item, $att) {
  $descr = $item->event_description;
  if (strlen($descr) < $att["teaser-length"]) {
    return $descr;
  }
  $shorter = substr($descr, 0, $att["teaser-length"]);
  $indexOfLastPeriod = strrpos($shorter, ".");
  if (!$indexOfLastPeriod) {
    return $shorter . "[...]";
  } else {
    return substr($shorter, 0, $indexOfLastPeriod + 1);
  }
}

function render_yesTicketSlideshowHelp() {?>
  <h2><?php echo __("Shortcodes for your events as slides.", "yesticket");?></h2>
  <p><?php echo __("quickstart", "yesticket");?>: <span class="yt-code">[yesticket_event_slides]</span></p>
  <p><?php echo __("Beware: This shortcode must be placed on a separate page. Visit that page and enter fullscreen mode to view your presentation.", "yesticket");?></p>
  <p><?php echo __("Running this presentation on a big screen, before/after a show, is a nice way to inform your audience about upcoming events.", "yesticket");?></p>
  <h3><?php echo __("Options for slideshow shortcodes", "yesticket");?></h3>

  <h4>Color-1</h4>
  <p class='ml-3'><?php echo __("'color-1' is used for the background of the welcome slide and as text color on other slides.", "yesticket");?></p>
  <p class="ml-3"><span class="yt-code">color-1="#fff000"</span></p>
  <p class="ml-3"><?php 
    echo __("Any valid css color definiton is okay. Use this option to bring in your company's style.", "yesticket");?>
  </p>
  <h4>Color-2</h4>
  <p class='ml-3'><?php echo __("'color-2' is used for the text color of the welcome slide and as box shadow color on other slides.", "yesticket");?></p>
  <p class="ml-3"><span class="yt-code">color-2="rgb(0, 80, 80)"</span></p>
  <p class="ml-3"><?php 
    echo __("Any valid css color definiton is okay. Use this option to bring in your company's style.", "yesticket");?>
  </p>
  
  <h4>Ms-Per-Slide</h4>
  <p class='ml-3'><?php echo __("Using 'ms-per-slide', you adjust the duration of each slide. The unit is milliseconds.", "yesticket");?></p>
  <p class="ml-3"><span class="yt-code">ms-per-slide="8000"</span><?php 
    echo __("will display each slide for 8 seconds.", "yesticket");?></p>

  <h4>Teaser-Length</h4>
  <p class='ml-3'><?php echo __("Using 'teaser-length', you can define the maximum characters of the descriptive text. The text is cut at the end of the last sentence that fits within the limit.", "yesticket");?></p>
  <p class="ml-3"><span class="yt-code">teaser-length="123"</span></p>

  <h4>Welcome-1 Welcome-2 Welcome-3</h4>
  <p class='ml-3'><?php echo __("Using 'welcome-1', 'welcome-2' and 'welcome-3', you can adjust the text on the welcome splash slide.", "yesticket");?></p>
  <p class="ml-3">
    <span class="yt-code">
      welcome-1="<?php echo __('welcome to our', "yesticket"); ?>"
      welcome-2="<?php echo __('improv theatre show', "yesticket"); ?>"
      welcome-3="<?php echo __('where everything is made up', "yesticket"); ?>"
    </span></br><?php 
    echo __("'welcome-2' defines the bigger text in the center.", "yesticket");?>
  </p>
  
  <h4>Text-Scale</h4>
  <p class='ml-3'><?php echo __("Using 'text-scale', you can change the font-size of the presentation.", "yesticket");?></p>
  <p class="ml-3"><span class="yt-code">text-scale="120%"</span></p>
  <p class='ml-3'><?php echo __("Check how small/big you need this value to be, so your audience can read the information well.", "yesticket");?></p><?php 

  echo ytp_render_optionType('events');
  echo ytp_render_optionCount();

} ?>
