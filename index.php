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
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.1/masonry.pkgd.js"></script>
    <script src="http://enscrollplugin.com/releases/enscroll-0.6.1.min.js"></script>
</head>
<body>
    <header>
        <img src="images/mimic-logo.png" width="170">
        <input type="text" id="inLocate" placeholder="Search current view ..." title="Names to search for (space or comma seperated)"/>
        <div class="scroll">
        <nav>
            <ul>
                <div class="drop-head">
                    <h4>Logical</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <li id="logical-workers" class="tab" title="Logical overview of worker nodes">Workers</li>
                <li id="logical-storage" class="tab" title="Logical overview of storage nodes">Storage</li>
            </ul>
            <ul>
                <div class="drop-head">
                    <h4>Generational</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <li id="generational-storage" class="tab" title="Generational overview of storage nodes">Storage</li>
                <li id="generational-all" class="tab" title="Generational overview of all nodes">Overview</li>
            </ul>
            <ul>
                <div class="drop-head">
                    <h4>Aquilon</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <li id="aquilon-sandboxes" class="tab" title="Nodes in aquilon domains and sandboxes">Sandboxes & Prod</li>
                <li id="aquilon-personalities" class="tab" title="Nodes with aquilon personalities">Personalities</li>
            </ul>
            <ul>
                <div class="drop-head">
                    <h4>Cloud</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <li id="cloud" class="tab" title="Overview of cloud nodes">Overview</li>
            </ul>
            <ul>
                <div class="drop-head">
                    <h4>Elasticsearch</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <li id="elasticsearch-routing" class="tab" title="Elasticsearch Routing Table">Routing</li>
                <li id="elasticsearch-hosts" class="tab" title="Elasticsearch Shards by Host">Hosts</li>
            </ul>
            <ul>
                <div class="drop-head">
                    <h4>Key</h4>
                    <span class="arrow glyphicon glyphicon-circle-arrow-down"></span>
                </div>
                <div class="key"></div>
            </ul>
        </nav>

        </div>
        <footer><p>Please report any bugs or issues to the mimic <a href="https://github.com/stfc/mimic">GitHub</a> repository</p>
        <a aria-label="Issue STFC/mimic on GitHub" data-count-aria-label="# issues on GitHub" data-count-api="/repos/STFC/mimic#open_issues_count" href="https://github.com/STFC/mimic/issues" class="github-button">Issues</a>
        </footer>

    </header>

    <!-- Gets content injected from 'js/key.js'-->


    <?php require 'inc/functions.inc.php'; ?>

    <div class="wrapper">
        <div id="farm"></div><!-- Nodes get rendered in here -->
    </div>
    <script>
    var view = 'logical-workers';
    $('#' + view).addClass('active');
    $loading = '<div class="loading"><img src="images/loading.svg" alt="loading" /></div>';
    $('#farm').html($loading);
    function update() {
        var requested_view = view;
        $.get('views/view-' + view + '.php').done(function (d) {
            if (view !== requested_view) {
                console.log('Ignoring callback for ' + requested_view + ', current view is ' + view);
                return;
            }
            $('#farm').html(d);
            locateNode($('#inLocate').val());
            $('span.node').tooltip({html: true, container: '#farm', placement: 'auto bottom'});
            // Gets the conditions for if a key should be shown or not
            $.getScript('js/key.js');
            // Increases width of panel if number of nodes is too high

            $.each($('.node-cluster'), function () {
                $con_width = $(this).children('.node').length;
                if ($con_width > 240) {
                    // alert($con_width);
                    $(this).parent().addClass('grid-item-big');
                }
                // Adds title with number of nodes in
                if ($(".cluster-name")[0]) {
                    $(this).children(".cluster-name").prop('title', "This section contains " + $con_width + " nodes");
                } else {
                    $(this).prev().prop('title', "This section contains " + $con_width + " nodes");
                }
            });
            $('.node-group').masonry({
                gutter: 16,
                itemSelector: '.grid-item',
                columnWidth: 228
            });
        });
    }
    //Refresh page
    window.setInterval(function (){
        update();
    }, 60000); // Every 60 seconds
    update();
    // Allows user to search
    $('#inLocate').keyup(function () {
        locateNode(this.value);
    });
    // Makes menu functional
    $('.tab').click(function () {
        $('.tab').removeClass('active');
        $(this).addClass('active');
        view = this.id;
        $("#farm").html($loading);
        update();
    });
    // Shows and hides key
    $('.drop-head').click(function () {
        $(this).children('span').toggleClass('glyphicon-circle-arrow-right').toggleClass('glyphicon-circle-arrow-down');
        $(this).siblings('li, div').slideToggle();
    });
    $('.scroll').enscroll({
        propagateWheelEvent: false,
        verticalScrollerSide: 'left',
        easingDuration: 300,
    });
    </script>
    <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
