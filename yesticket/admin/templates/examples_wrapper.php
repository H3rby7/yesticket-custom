<?php $activeTab = isset($_GET['tab']) ? $_GET['tab'] : null; ?>
<div class="ytp-admin">
  <h2><?php _e("Shortcodes", "yesticket"); ?></h2>
  <p>
    <?php _e("You can use multiple shortcodes on your page. For example you might start with a list of your shows, followed by your workshops and finish with testimonials of your audience.", "yesticket"); ?>
  </p>
  <p><?php
      /* translators: Hint text on plugin-page to preview different shortcodes */
      _e("Hover above the tabs for a preview.", "yesticket");
      ?></p>
  <nav class="nav-tab-wrapper ytp-admin-nav-wrapper">
    <?php $this->render_navigation_tab(
      "",
      $activeTab,
      /* translators: Refers to the 'yesticket_events' shortcode */
      __("Events", "yesticket"),
      "yesticket_events",
      "sample_events.png"
    ); ?>
    <?php $this->render_navigation_tab(
      "cards",
      $activeTab,
      /* translators: Refers to the 'yesticket_events_cards' shortcode */
      __("Cards", "yesticket"),
      "yesticket_events_cards",
      "sample_events_cards.png"
    ); ?>
    <?php $this->render_navigation_tab(
      "list",
      $activeTab,
      /* translators: Refers to the 'yesticket_events_list' shortcode */
      __("List", "yesticket"),
      "sample_events_list",
      "sample_events_list.png"
    ); ?>
    <?php $this->render_navigation_tab(
      "testimonials",
      $activeTab,
      /* translators: Refers to the 'yesticket_testimonials' shortcode */
      __("Testimonials", "yesticket"),
      "yesticket_testimonials",
      "sample_testimonials.png"
    ); ?>
    <?php $this->render_navigation_tab(
      "slides",
      $activeTab,
      /* translators: Refers to the 'yesticket_slides' shortcode */
      __("Slides", "yesticket"),
      "yesticket_slides",
      "sample_slides.png"
    ); ?>
  </nav>
  <div class="ytp-admin-tab-content">
    <?php $this->render_tabContent($activeTab); ?>
  </div>
</div>