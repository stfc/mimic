<?php

class pRequestTracker
{
  function header($NODE, $SHORT)
  {
    return(Array("RT Tickets"));
  }

  function detail($NODE, $SHORT)
  {
    ?>
    <div id="requestTrackerDetails">
    <p class="loading">Loading...</p>
    </div>
    <script type="text/javascript">
        function requestTrackerCallbackDisplay(d, s, x) {
            var data = $.parseJSON(d);
            var found = false;
            var html = "<table>\n<tr><th>Id</th><th>Queue</th><th>Subject</th><th>Created</th></tr>\n";

            var keys = Object.keys(data).sort();

            if (keys.length > 0) {
                for (i in keys) {
                    j = keys[i];
                    r = data[j];
                    html += '<tr class="node-RT-'+r[3]+'">';
                    html += '<td><a href="https://helpdesk.example.com/Ticket/Display.html?id='+j+'">'+j+'</a></td>';
                    html += '<td>'+r[1]+'</td>';
                    html += '<td>'+r[0]+'</td>';
                    html += '<td>'+r[2]+'</td>';
                    html += '</tr>\n';
                }
                html += '</table>\n';

                $("#requestTrackerDetails").html(html);
            }
            else {
                $("#requestTrackerDetails").html("<p class=\"info\">None found</p>\n");
            }
        }
        $(function() {
            $.get("components/node-requesttracker-rest.php", { "name" : "<?php echo $SHORT; ?>", "time" : Date.now() }, requestTrackerCallbackDisplay);
        });
    </script>
    <?php

    echo "      <p>\n";
    echo "        New\n";
    echo "        <a href=\"https://helpdesk.example.com/Ticket/Create.html?Queue=Fabric&amp;Subject=".htmlspecialchars($NODE)."\">Fabric</a>\n";
    echo "        <a href=\"https://helpdesk.example.com/Ticket/Create.html?Queue=Fabric-Hardware&amp;Subject=".htmlspecialchars($NODE)."\">Hardware</a>\n";
    echo "        Ticket\n";
    echo "      </p>\n";
  }
}

return new pRequestTracker();
?>
