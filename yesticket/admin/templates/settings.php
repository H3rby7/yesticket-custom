<?php $activeTab = isset($_GET['tab']) ? $_GET['tab'] : null;?>
<div class="ytp-admin">
  <?php echo $this->feedback(); ?>
  <nav class="nav-tab-wrapper ytp-admin-nav-wrapper">
    <?php $this->render_navigation_tab("", $activeTab, "Required"); ?>
    <?php $this->render_navigation_tab("technical", $activeTab, "Technical"); ?>
  </nav>
  <div class="ytp-admin-tab-content">
    <?php $this->render_tabContent($activeTab); ?>
  </div>
</div>