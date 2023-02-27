<?php $activeTab = isset($_GET['tab']) ? $_GET['tab'] : null; ?>
<div class="ytp-admin">
  <?php $this->feedback(); ?>
  <nav class="nav-tab-wrapper ytp-admin-nav-wrapper">
    <?php $this->render_navigation_tab(
      "",
      $activeTab,
      /* translators: The word refers to settings -> e.G. "Required [Settings]'*/
      \__("Required", "yesticket")
    ); ?>
    <?php $this->render_navigation_tab(
      "technical",
      $activeTab,
      /* translators: The word refers to settings -> e.G. "Technical [Settings]'*/
      \__("Technical", "yesticket")
    ); ?>
  </nav>
  <div class="ytp-admin-tab-content">
    <?php $this->render_tabContent($activeTab); ?>
  </div>
</div>