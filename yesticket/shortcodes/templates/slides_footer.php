</article>
</main>
<script>
  window.addEventListener('load', function() {
    window.ws = new WebSlides({
      autoslide: <?php echo $att["ms-per-slide"]; ?>
    });
  }, false);
</script>