<!DOCTYPE html>
<?php

require('components/functions.inc.php');
require('components/db-open.inc.php');
require('components/node-getName.inc.php');

?>
<html lang="en">
  <head>
    <title>Tier1A monitor: <?php echo htmlspecialchars($NODE) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="300" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <link rel="stylesheet" type="text/css" href="info.css" media="screen" />
    <script type="text/javascript" src="components/plugins.js"></script>
  </head>
  <body>
<?php
  //Header
  include('components/node-header.inc.php');

  //Set custom error handler so plugins stand less chance of killing everything
  set_error_handler("fPluginFail");

  require('config/plugins.inc.php');

  foreach ($plugins as $p) {
    $plugfile = "components/node-$p.inc.php";
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

  //This needs to be called before anything else that needs the castorInstance info
  /*
  $castorInstance = $oOverwatch -> getDBinfo($SHORT);
  if ($castorInstance !== null) {
    $castorInstance = $castorInstance['castorInstance'];
  }

  include('components/node-pakiti2-json.inc.php');  //Pakiti2 package info
  $oPakiti2   = new pPakiti2();
  $oPakiti2   -> detail($NODE, $SHORT);

  include('components/node-ganglia.inc.php');   // Ganglia info
  $oGanglia = new pGanglia();
  $oGanglia -> detail($NODE, $SHORT, $castorInstance);
*/
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
