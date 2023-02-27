<form method="post" action="<?php echo $action; ?>">
  <?php
  \settings_fields($this->get_slug());
  \do_settings_sections($this->get_slug());
  \submit_button();
  ?>
</form>
<?php $this->render_clear_cache_button(); ?>