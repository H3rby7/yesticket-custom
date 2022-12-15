<?php 
// $type could be "Events" or "Testimonials"
function ytp_render_optionType($type) {?>
    <h4>Type</h4>
    <p class='ml-3'>Mit <b>type</b> kannst du deine <?php echo $type; ?> nach Art filtern.</p>
    <p class="ml-3"><span class="yt-code">type="performance"</span> nur vom Typ Auftritte<br>
    <span class="yt-code">type="workshop"</span> nur vom Typ Workshops<br>
    <span class="yt-code">type="festivals"</span> nur vom Typ Festivals<br>
    <span class="yt-code">type="all"</span> Alles, gemischt</p>
<?php } 
function ytp_render_optionTheme() {?>
    <h4>Theme</h4>
    <p class='ml-3'>Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.</p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> Buttons sind Hellgrau und passen zu hellen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> Buttons sind Dunkelgrau und passen zu dunklen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme=""</span> Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.</p>
<?php }
function ytp_render_optionCount() {?>
  <h4>Count</h4>
  <p class='ml-3'>Mit <b>count</b> kannst du die maximale Anzahl der gezeigten Elemente bestimmen.</p>
  <p class="ml-3"><span class="yt-code">count="6"</span> werden maximal 6 kommende Events angezeigt</p>
  <p>Beachte, dass es sich um eine Maximalzahl handelt. Wenn also nicht genügend Elemente vorhanden sind, werden weniger angezeigt.</p>
<?php }