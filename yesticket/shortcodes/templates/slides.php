<style>
  #ytp-slides {
    --ytp--color--primary: <?php echo $att['color-1']; ?>;
    --ytp--color--contrast: <?php echo $att['color-2']; ?>;
    font-size: <?php echo $att['text-scale']; ?>;
  }
</style>
<div id='ytp-slides'>
  <main role="main">
    <article id="webslides">
      <?php echo $this->render_template("slides_welcome", compact("att")); ?>
      <?php
      foreach ($result as $event) :
        echo $this->render_template("slides_item", compact("event", "att"));
      endforeach
      ?>
    </article>
  </main>
  <script>
    window.addEventListener('load', function() {
      window.ws = new WebSlides({
        autoslide: <?php echo $att["ms-per-slide"]; ?>
      });
    }, false);
  </script>
</div>
<?php
