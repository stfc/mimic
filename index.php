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
include('config/plugins.inc.php');
?>
<html>
<head>
    <title>Mimic</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/jquery.cookiebar.css" />

    <script type="text/javascript" src="js/bower.js"></script>
    <script type="text/javascript" src="js/monitor.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Emilys+Candy' rel='stylesheet' type='text/css'>
</head>
<body>
    <?php
    require("config/menu-config.php");

    function loadMenu($menu) {
        foreach($menu as $section => $item) {
            echo "<ul><div class='drop-head' onclick=\"toggleRollup('#$section');\"><h4>$section</h4>";
            if (filter_input(INPUT_COOKIE, 'rollup_#'.$section.'', FILTER_SANITIZE_STRING) == "hidden") {
                echo "<span class='arrow glyphicon glyphicon-circle-arrow-right'></span>";
            } else {
                echo "<span class='arrow glyphicon glyphicon-circle-arrow-down'></span>";
            }
            echo "</div>";

            echo "<div id='$section' class='menu-items'";
            if (filter_input(INPUT_COOKIE, 'rollup_#'.$section.'', FILTER_SANITIZE_STRING) == "hidden") {
                echo " style='display: none'";
            }
            echo ">";

            foreach($item as $item_name => $item_info) {
                if (array_key_exists('other', $item_info)) {
                    echo $item_info['other'];
                } else {
                    echo "<li id='{$item_info['link']}' class='tab' title='{$item_info['text']}'>{$item_info['name']}</li>";
                }
            }
            echo "</div></ul>";
        }
    }
    ?>

    <aside>
        <!-- Header -->
        <header>
            <div class="logo">Mimic</div>
            <input type="text" id="inLocate" placeholder="Search current view ..." title="Names to search for (space or comma seperated)"/>
        </header>

        <!-- Menu -->
        <div class="scroll">
            <nav>
                <!-- Menu -->
                <?php loadMenu($menu);?>
                <!-- Footer -->
                <footer><a aria-label="Issue STFC/mimic on GitHub" data-count-aria-label="# issues on GitHub" data-count-api="/repos/STFC/mimic#open_issues_count" href="https://github.com/STFC/mimic/issues" class="github-button">GitHub</a></footer>
            </nav>
        </div>
    </aside>

    <!-- Main content -->
    <div class="wrapper">
        <div id="farm"><!-- Nodes get rendered in here --></div>
    </div>

    <script type="text/javascript">
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
    });
    $('.scroll').enscroll({
        propagateWheelEvent: false,
        verticalScrollerSide: 'left',
        easingDuration: 300,
        addPaddingToPane: false,
    });

    </script>

    <!-- Cookie compliance  -->
    <script type="text/javascript">
    $(document).ready(function(){
        $.cookieBar({
            message: 'We use cookies to remember your preferences',
            acceptText: 'Cool! On with the show!',
            autoEnable: false,
            fixed: true,
            zindex: '1',
        });
    });
    </script>
    <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
