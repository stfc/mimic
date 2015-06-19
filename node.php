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

  <link rel="stylesheet" type="text/css" href="components/cookiecuttr/cookiecuttr.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="components/themes/base/jquery.ui.all.css" media="screen" />
  <script type="text/javascript" src="components/jquery-1.8.0.min.js"></script>
  <script type="text/javascript" src="components/jquery.cookie.js"></script>
  <script type="text/javascript" src="components/cookiecuttr/jquery.cookiecuttr.js"></script>
  <script type="text/javascript" src="components/ui/jquery.ui.core.min.js"></script>
  <script type="text/javascript" src="components/ui/jquery.ui.position.min.js"></script>
  <script type="text/javascript" src="components/ui/jquery.ui.widget.min.js"></script>
  <script type="text/javascript" src="components/ui/jquery.ui.dialog.min.js"></script>
  <script type="text/javascript" src="components/ui/jquery.effects.core.min.js"></script>
  <script type="text/javascript" src="components/ui/jquery.effects.blind.min.js"></script>
  <script type="text/javascript" src="js/plugins.js"></script>
</head>
<body>
  <?php
  //Header
  include('node/node-header.inc.php');

  //Set custom error handler so plugins stand less chance of killing everything
  set_error_handler("fPluginFail");

  include('config/plugins.inc.php');

  foreach ($plugins as $p) {
    $plugfile = "node/node-$p.inc.php";
    if (file_exists($plugfile)) {
      $plug = include($plugfile);
      if (is_object($plug)) {
        echo "<div class=\"sub\">\n";
        echo "<h2>\n";

        $header = $plug -> header($NODE, $SHORT);
        if (!is_array($header)) {
          $header = Array($header);
        }

        echo "<span class=\"rollup\" onclick=\"toggleRollup('#$p');\" title=\"Rollup Section\">&#x25BE;&nbsp;";
        echo array_shift($header)."\n";
        echo "</span>\n";

        foreach ($header as $h) {
          echo "$h\n";
        }

        echo "</h2>\n";

        echo "<div id=\"$p\"";
        if (isset($_COOKIE["rollup_#$p"]) and $_COOKIE["rollup_#$p"] == "hidden") {
          echo " style=\"display: none\"";
        }
        echo ">\n";
        $plug -> detail($NODE, $SHORT);
        echo "</div>\n";
        echo "</div>\n";
      } else {
        echo '<div class="sub" id="'.$p.'">';
        echo '<p class="warning">Plugin file for plugin "'.$p.'" does not contain a valid plugin.</p>';
        echo "</div>\n";
      }
    } else {
      echo '<div class="sub" id="'.$p.'">';
      echo '<p class="warning">Could not find plugin file for plugin "'.$p.'".</p>';
      echo "</div>\n";
    }
  }

  //Put error handler back
  restore_error_handler();

  ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $.cookieCuttr({
        "cookieDeclineButton" : true,
        "cookieAnalyticsMessage" : "We use cookies to store which sections you have visible."
      });
    });
  </script>
</body>
</html>
