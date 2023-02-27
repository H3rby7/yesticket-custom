<details class="ytp-event-details-details">
  <summary class="ytp-event-details-summary"><?php \_e("Show details", "yesticket"); ?></summary>
  <div>
    <?php echo \nl2br(\htmlentities($item->event_description), FALSE); ?>
    <h5><?php \_e("Hints", "yesticket"); ?></h5>
    <?php echo \nl2br(\htmlentities($item->event_notes_help)); ?>
    <h5><?php \_e("Tickets", "yesticket"); ?></h5>
    <?php echo \nl2br($item->tickets); ?>
    <h5><?php \_e("Location", "yesticket"); ?></h5>
    <?php echo $this->render_template('event_details_location', \compact("item")); ?>
  </div>
  <a class="ytp-event-details-back-to-top" href="#<?php $this->render_event_id($item); ?>">
    <?php \_e("Back to top", "yesticket"); ?>
  </a>
</details>