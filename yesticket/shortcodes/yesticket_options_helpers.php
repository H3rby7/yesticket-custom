<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

// $type could be "Events" or "Testimonials"
function ytp_render_optionType($type) {?>
    <h4>Type</h4>
    <p class='ml-3'><?php ytp_translate('Mit <b>type</b> kannst du deine '.$type.' nach Art filtern.');?></p>
    <p class="ml-3"><span class="yt-code">type="performance"</span> <?php ytp_translate('nur vom Typ Auftritte');?><br>
    <span class="yt-code">type="workshop"</span> <?php ytp_translate('nur vom Typ Workshops');?><br>
    <span class="yt-code">type="festivals"</span> <?php ytp_translate('nur vom Typ Festivals');?><br>
    <span class="yt-code">type="all"</span> <?php ytp_translate('Alles, gemischt');?></p>
<?php } 
function ytp_render_optionTheme() {?>
    <h4>Theme</h4>
    <p class='ml-3'><?php ytp_translate('Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.');?></p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> <?php ytp_translate('Buttons sind Hellgrau und passen zu hellen Hintergründen');?></p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> <?php ytp_translate('Buttons sind Dunkelgrau und passen zu dunklen Hintergründen');?></p>
    <p class="ml-3"><span class="yt-code">theme=""</span> <?php ytp_translate('Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.');?></p>
<?php }
function ytp_render_optionCount() {?>
  <h4>Count</h4>
  <p class='ml-3'><?php ytp_translate('Mit <b>count</b> kannst du die maximale Anzahl der gezeigten Elemente bestimmen.');?></p>
  <p class="ml-3"><span class="yt-code">count="6"</span> <?php ytp_translate('werden maximal 6 kommende Events angezeigt');?></p>
  <?php 
  ytp_p('Beachte, dass es sich um eine Maximalzahl handelt. Wenn also nicht genügend Elemente vorhanden sind, werden weniger angezeigt.');
}