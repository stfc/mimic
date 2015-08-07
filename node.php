<!DOCTYPE html>
<?php

$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require("inc/config-call.inc.php");
require('inc/functions.inc.php');
require('inc/db-open.inc.php');
require('node/node-getName.inc.php');
global $NODE;
global $SHORT;
?>
<html lang="en">
<head>
    <title>Tier1A monitor: <?php echo htmlspecialchars($NODE) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="300" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="css/info.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/jquery.cookiebar.css" />
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="js/jquery.cookiebar.js"></script>
    <script type="text/javascript" src="js/js.cookie-2.0.0.min.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
</head>
<body>
<?php
    //Header
    include('node/node-header.inc.php');

    //Set custom error handler so plugins stand less chance of killing everything
    set_error_handler("fPluginFail");

    include('config/plugins.inc.php');
    echo "<div class='wrapper'>";
    foreach ($plugins as $plugin) {
        $plugfile = "node/node-$plugin.inc.php";
        if (file_exists($plugfile)) {
            $plug = include($plugfile);
            if (is_object($plug)) {

                echo "<section>";

                // HEADER START
                echo "<h2>";

                $header = $plug -> header($NODE, $SHORT);
                // if (!is_array($header)) {
                //     $header = Array($header);
                // }
                header();
                echo "<span ";
                if ($test === true) {
                    echo "class=\"rollup\" onclick=\"toggleRollup('#$plugin');\"";
                }
                echo "title=\"Rollup Section\">&#x25BE;&nbsp;";
                echo array_shift($header)."";
                echo "</span>";


                foreach ($header as $headerInfo) {
                    echo "$headerInfo";
                }

                echo "</h2>";

                // HEADER END


                echo "<div id=\"$plugin\"";
                if (filter_input(INPUT_COOKIE, 'rollup_#'.$plugin.'', FILTER_SANITIZE_STRING) == "hidden") {
                    echo " style=\"display: none\"";
                }
                echo ">";
                $plug -> detail($NODE, $SHORT);
                echo "</div>";
                echo "</section>";
            } else {
                echo '<div class="content" id="'.$plugin.'">';
                echo '<p class="warning">Plugin file for plugin "'.$plugin.'" does not contain a valid plugin.</p>';
                echo "</div>";
            }
        } else {
            echo '<div class="sub" id="'.$plugin.'">';
            echo '<p class="warning">Could not find plugin file for plugin "'.$plugin.'".</p>';
            echo "</div>";
        }
    }
    echo "</div>";
    //Put error handler back
    restore_error_handler();

?>
<script type="text/javascript">
$(document).ready(function(){
    $.cookieBar({
        message: 'We use cookies to remember your preferences',
        acceptText: 'Cool! On with the show!',
        autoEnable: false,
        fixed: true,
        zindex: '100',
    });
});
</script>
</body>
</html>
