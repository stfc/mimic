<?php
class pAquilon {
  function header($NODE, $SHORT) {
    return(Array("Quattor (Aquilon)"));
  }
  function detail($NODE, $SHORT) {
    global $CONFIG;

    echo "<div id='aquilonDetails'>";

    $info = False;
    $url = $CONFIG['URL']['AQUILON'] . ":6901/host/".urlencode($NODE);

    $headers = get_headers($url);
    $status_code = substr($headers[0], 9, 3);
    if ($status_code == 200) {
      $info = explode("\n", file_get_contents($url));
      array_push($info, "");
      $p_in = -1;
      foreach ($info as $info_item) {
        $info_data = strlen($info_item) - strlen(ltrim($info_item));
        $info_item = explode(":", $info_item, 2);
        if ($info_data == $p_in) {
          echo "</li>\n";
        }
        if ($info_data > $p_in) {
          echo "\n<ul>\n";
        }
        elseif ($info_data < $p_in) {
          echo "\n</ul>\n";
        }
        if (sizeof($info_item) == 2) {
          $key = $info_item[0];
          $val = htmlentities($info_item[1]);
          $val = preg_replace('/(.*) &lt;(.+@.+)&gt;/', '<a href="mailto:$2">$1</a>', $val);
          $val = str_replace("[", "<em>[", $val);
          $val = str_replace("]", "]</em>", $val);
          echo "<li><strong>$key</strong> &ndash; $val";
        }
        $p_in = $info_data;
      }
      echo "</ul>\n";
    }
    else {
      echo "<p class=\"info\">No info for host.</p>\n";
    }

    echo "</div>";

  }
}
return new pAquilon();
