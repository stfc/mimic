<!DOCTYPE html>
<?php
  $node = filter_input(INPUT_GET, 'node', FILTER_SANITIZE_STRING);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Tier1A monitor: <?php echo htmlspecialchars($node) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="300" />
    <link rel="stylesheet" type="text/css" href="../css/info.css" media="screen" />
  </head>
  <body>
<?php
    preg_match('/[A-Za-z0-9.]+/', $node, $node); //Only count hostname characters
    if (is_array($node)) {
      $node = $node[0];
      if ($node != "") {
        echo "    <div class=\"head\">Ping ".htmlspecialchars(urlencode($node))."</div>\n";
        flush();
        ob_flush();
        echo "    <pre>\n";
        system('ping -c 5 '.urlencode($node));
        echo "    </pre>\n";
      }
      else {
        echo "     <p class=\"error\">Blank hostname provided</p>\n";
      }
    }
    else {
      echo "     <p class=\"error\">Invalid hostname</p>\n";
    }
  }
  else {
    echo "     <p class=\"error\">No hostname provided</p>\n";
  }
?>
  </body>
</html>
