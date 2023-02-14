<script>
  window.addEventListener('load', function() {
    window.ws = new WebSlides({
      autoslide: <?php echo $autoslide; ?>
    });
  }, false);
</script>