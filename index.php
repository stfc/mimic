<?php
  require("inc/header.inc.php");
?>
    <script>
      var view = "batch";
      $("#"+view).addClass("active");
      var msg_loading = '<div class="message"><img src="images/loading.png" alt="loading" /></div>';
      $("#farm").html(msg_loading);
      function update() {
        $.get("views/view-"+view+".php", function(d) {
          $("#farm").html(d);
          locateNode($("#inLocate").val());
          $('span.node').tooltip({html: true, container: '#farm', placement: 'bottom'});
        });
      }
      window.setInterval("update()", 60000);
      update();
    </script>
<?php
  require('inc/footer.inc.php');
?>
