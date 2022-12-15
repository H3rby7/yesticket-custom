<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

// $type could be "Events" or "Testimonials"
function ytp_render_optionType($type) {?>
    <h4>Type</h4>
    <?php if ($type === 'events') { ?>
      <p class='ml-3'><?php echo __('Mit <b>type</b> kannst du deine Events nach Art filtern.', 'yesticket');?></p>
    <?php } elseif ($type === 'testimonials') { ?>
      <p class='ml-3'><?php echo __('Mit <b>type</b> kannst du deine Testimonials nach Art filtern.', 'yesticket');?></p>
    <?php } else  {
      throw new AssertionError('Expect argument "$type" to be either "events" or "testimonials"!');
    }?>
    <p class="ml-3"><span class="yt-code">type="performance"</span> <?php echo __('nur vom Typ Auftritte', 'yesticket');?><br>
    <span class="yt-code">type="workshop"</span> <?php echo __('nur vom Typ Workshops', 'yesticket');?><br>
    <span class="yt-code">type="festivals"</span> <?php echo __('nur vom Typ Festivals', 'yesticket');?><br>
    <span class="yt-code">type="all"</span> <?php echo __('Alles, gemischt', 'yesticket');?></p>
<?php } 
function ytp_render_optionTheme() {?>
    <h4>Theme</h4>
    <p class='ml-3'><?php echo __('Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> <?php echo __('Buttons sind Hellgrau und passen zu hellen Hintergründen', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> <?php echo __('Buttons sind Dunkelgrau und passen zu dunklen Hintergründen', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme=""</span> <?php echo __('Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.', 'yesticket');?></p>
<?php }
function ytp_render_optionCount() {?>
  <h4>Count</h4>
  <p class='ml-3'><?php echo __('Mit <b>count</b> kannst du die maximale Anzahl der gezeigten Elemente bestimmen.', 'yesticket');?></p>
  <p class="ml-3"><span class="yt-code">count="6"</span> <?php echo __('werden maximal 6 kommende Events angezeigt', 'yesticket');?></p>
  <p><?php echo __('Beachte, dass es sich um eine Maximalzahl handelt. Wenn also nicht genügend Elemente vorhanden sind, werden weniger angezeigt.', 'yesticket');?></p><?php
}