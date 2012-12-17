<?php
  require("components/header.inc.php");
?>
    </div>
    <script>
      var view = "batch";
      $("#"+view).addClass("active");
      var msg_loading = '<div class="message"><img src="images/loading.png" alt="loading" /></div>';
      $("#farm").html(msg_loading);
      function update() {
        $.get("views/"+view+".php", function(d) {
          $("#farm").html(d);
          locateNode($("#inLocate").val());
        });
      }
      window.setInterval("update()", 60000);
      update();
    </script>
<?php
  require('components/footer.inc.php');
?>
