<?php

class pRequestTracker
{
  function header($NODE, $SHORT)
  {
    return(Array("RT Tickets"));
  }

  function detail($NODE, $SHORT)
  {
    global $CONFIG;
    $HELPDESK_URL = $CONFIG['URL']['HELPDESK'];
    ?>
    <div id="requestTrackerDetails">
    <p class="loading">Loading...</p>
    </div>
    <script type="text/javascript">
        function requestTrackerCallbackDisplay(d, s, x) {

            var data = $.parseJSON(d);
            var found = false;
            var html = "<table>\n<tr><th>Id</th><th>Queue</th><th>Subject</th><th>Created</th></tr>\n";

            var keys = Object.keys(data).sort(function(a,b){return b-a}); //Reverse numeric sort

            if (keys.length > 0) {
                for (i in keys) {
                    j = keys[i];
                    r = data[j];
                    html += '<tr class="node-RT-'+r[3]+'">';
                    html += '<td><a href="<?php echo $HELPDESK_URL ?>/Ticket/Display.html?id='+j+'">'+j+'</a></td>';
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
            $.get("node/node-requesttracker-rest.php", { "name" : "<?php echo $SHORT; ?>", "time" : Date.now() }, requestTrackerCallbackDisplay);
        });
    </script>
    <?php
    $node = htmlspecialchars($NODE);

    echo "      <p>\n";
    echo "        New\n";
    echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Fabric&amp;Subject=$node\">Fabric</a>,\n";
    echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Fabric-Hardware&amp;Subject=$node\">Hardware</a>,\n";
    $body = urlencode(
        "As service owner of $node I am requesting that it be decommissioned as per the procedure documented at:\n" .
        "https://wiki.e-science.cclrc.ac.uk/web1/bin/view/EScienceInternal/GeneralProcedureForDecommissioningServers\n" .
        "\n" .
        "The hardware should be retired and disposed of.\n" .
        "The hardware should be put into holding for redeployment.\n" .
        "(Delete as appropriate)."
    );
    echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Support&amp;Subject=$node%20Server%20Decommissioning&amp;Content=$body\">Decommissioning</a>\n";
    echo "        Ticket\n";
    echo "      </p>\n";
  }
}

return new pRequestTracker();
?>
