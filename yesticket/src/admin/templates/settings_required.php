<form method="post" action="<?php echo $action; ?>">
  <input type='hidden' name='option_page' value='<?php echo esc_attr($this->get_slug()); ?>' />
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($request_url); ?>" />
  <?php
  wp_nonce_field($this->get_slug() . "-options", "_wpnonce", false);
  do_settings_sections($this->get_slug());
  submit_button();
  ?>
</form>