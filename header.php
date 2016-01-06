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
if (filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING))
$page = mysql_escape_string($_REQUEST['page']);
else
$page = 1;
require("inc/config-call.inc.php");
?>
<html>
<head>
    <title>Mimic</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" href="assets/dist/images/icon.png" type="image/png" />
    <link rel="stylesheet" href="assets/dist/css/style.min.css">
    <link href='https://fonts.googleapis.com/css?family=Emilys+Candy' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="assets/dist/js/script.min.js"></script>
</head>
<body>
    <?php
    require("config/menu-config.php");

    function loadMenu($menu) {
        foreach($menu as $section => $item) {
            echo "<ul class='menu-section'><div class='drop-head' onclick=\"toggleRollup('#$section');\"><h4 class='menu-header'>$section</h4>";
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
                <div class="time"></div>
            </nav>
        </div>
    </aside>
