<div>
  <form method="post" action="<?php echo $action; ?>">
    <?php
    \settings_fields($this->get_slug());
    \do_settings_sections($this->get_slug());
    \submit_button();
    ?>
  </form>
  <form action="admin.php?page=<?php echo $this->get_parent_slug(); ?>" method="post">
    <input type="hidden" name="clear-cache" value="1" />
    <label for="clear-cache_submit"><?php
                                    /* translators: The sentence ends with a button 'Clear Cache' (can be translated at that msgId) */
                                    _e("If your changes in YesTicket are not reflected fast enough, try to: ", "yesticket");
                                    ?></label>
    <input type="submit" name="clear-cache_submit" value="<?php
                                                          /* translators: Text on a button, use imperativ if possible. */
                                                          _e("Clear Cache", "yesticket");
                                                          ?>" />
  </form>
</div>