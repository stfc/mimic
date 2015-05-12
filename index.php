<?php
$path = '/var/www/html/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require 'inc/header.inc.php';
?>
<script>

  var view = 'batch';
  $('#'+view).addClass('active');
  var msg_loading = '<div class="message"><img src="images/loading.png" alt="loading" /></div>';
  $('#farm').html(msg_loading);

  function update() {
    var requested_view = view;
    var request = $.get('views/view-'+view+'.php').done(function(d) {
      if (view != requested_view) {
        console.log("Ignoring callback for "+requested_view+", current view is "+view);
        return;
      }
      $("#farm").html(d);
      locateNode($('#inLocate').val());
      $('span.node').tooltip({html: true, container: '#farm', placement: 'bottom'});

      $.each($('div.cluster, div.instance'), function() {
        $con_width = $(this).children('span.node').length;
        if ($con_width <= 30) {
          $(this).addClass('width-small');
        }
        else if ($con_width > 30 && $con_width < 100) {
          $(this).addClass('width-col6');
        }
        else if ($con_width > 100 && $con_width < 200) {
          $(this).addClass('width-col3');
        }
        else if ($con_width > 500) {
          $(this).addClass('width-col1');
        };

      });
    });
  }
  //Refresh page
  window.setInterval('update()', 60000);
  update();
</script>
<?php require 'inc/footer.inc.php';?>
