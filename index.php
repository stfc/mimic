<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
?>
<!DOCTYPE html>
<?php
# Some variables to get us started...
$NODES = array (); # For names from csf_monitor
$SHORT = array (); # For names from nagios

// Which page are we viewing?
if (isset($_REQUEST['page']))
  $page = mysql_escape_string($_REQUEST['page']);
else
  $page = 1;
require("inc/config-call.inc.php");
?>
<html>
<head>
    <title>Tier1 Mimic</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <script type="text/javascript" src="js/monitor.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>
<header>
    <nav id="menu">
        <li id="logical-workers" class="tab menu-link" title="Logical overview of worker nodes">Logical - Workers</li>
        <li id="logical-storage" class="tab menu-link" title="Logical overview of storage nodes">Logical - Storage</li>
        <li id="generational-storage" class="tab menu-link" title="Generational overview of storage nodes">Generational - Storage</li>
        <li id="generational-all" class="tab menu-link" title="Generational overview of all nodes">Generational - All</li>
        <li id="aquilon-sandboxes" class="tab menu-link" title="Nodes in aquilon domains and sandboxes">AQ Sandboxes</li>
        <li id="aquilon-personalities" class="tab menu-link" title="Nodes with aquilon personalities">AQ Personalities</li>
        <li id="cloud" class="tab menu-link" title="Overview of cloud nodes">Cloud</li>
        <li id="elasticsearch-routing" class="tab menu-link" title="Elasticsearch Routing Table">ES Routing</li>
        <li id="elasticsearch-hosts" class="tab menu-link" title="Elasticsearch Shards by Host">ES Hosts</li>
        <input type="text" id="inLocate" placeholder="Search by name" title="Names to search for (space or comma seperated)"/>
        <li id="key-button" class="tab" title="Node colour key"><img src="images/icons/key.png"></li>
    </nav>
</header>

<!-- Gets content injected from 'js/key.js'-->
<div id="key"><ul class="key-dropdown"></ul></div>

<?php require 'inc/functions.inc.php'; ?>

<div id="farm"><!-- Nodes get rendered in here --></div>
<script>
    var view = 'logical-workers';
    $('#'+view).addClass('active');
    var msg_loading = '<div class="message"><img src="images/loading.svg" alt="loading" /></div>';
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

            $.getScript("js/key.js"); // Gets the conditions for if a key should be shown or not

            $.each($('div.cluster, div.instance'), function() {
                $con_width = $(this).children('span.node').length;
                if ($con_width <= 40) {
                    $(this).addClass('width-small');
                }
                else if ($con_width > 40 && $con_width < 100) {
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

    // Allows user to search
    $("#inLocate").keyup(function(e) {
        locateNode(this.value);
    });
    // Makes menu functional
    $(".menu-link").click(function(e) {
        $(".menu-link").removeClass("active");
        $(this).addClass("active");
        view = this.id;
        $("#farm").html(msg_loading);
        update();
    });
    // Shows and hides key
    $("#key-button").click(function(e) {
        $(this).toggleClass("active");
        $("#key").slideToggle();
    });
    </script>
</body>
</html>
