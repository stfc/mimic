<?php

class pAquilon
{
  function header($NODE, $SHORT)
  {
    return(Array("Quattor (Aquilon)"));
  }

  function detail($NODE, $SHORT)
  {
    $HARDTRACK_URL = "http://hardtrack.example.com";
    ?>
    <div id="aquilonDetails">
       <?php
       $info = False;
       $url = "http://aquilon.example.com:6901/host/".urlencode($NODE);

       $h = get_headers($url);
       $h = substr($h[0], 9, 3);
       if ($h == 200) {
         $info = explode("\n", file_get_contents($url));
         array_push($info, "");
         $p_in = -1;
         foreach ($info as $i) {
           $in = strlen($i) - strlen(ltrim($i));
           $i = explode(":", $i, 2);
           if ($in == $p_in) {
             echo "</li>\n";
           }
           if ($in > $p_in) {
             echo "\n<ul>\n";
           }
           elseif ($in < $p_in) {
             echo "\n</ul>\n";
           }
           if (sizeof($i) == 2) {
             $key = $i[0];
             $val = $i[1];
             $val = str_replace("[", "<em>[", $val);
             $val = str_replace("]", "]</em>", $val);
             $val = preg_replace("/([a-zA-Z0-9]+: )(.*)/", '<ul><li><strong>$1</strong> &ndash; $2</li></ul>', $val);
             $val = preg_replace("/(([a-zA-Z0-9]+\.){2,}[a-zA-Z0-9]+)/", '<a href="http://$1">$1</a>', $val);
             $val = preg_replace("/system([0-9]+)/", "<a href=\"$HARDTRACK_URL/?section=system&amp;a=$1\" title=\"View platform $1 in HardTrack\">system$1</a>", $val);
             echo "<li><strong>$key</strong> &ndash; $val";
           }
           $p_in = $in;
         }
         echo "</ul>\n";
       }
       else {
         echo "<p class=\"info\">No info for host.</p>";
       }
      ?>
    </div>
    <?php
  }
}

return new pAquilon();
?>
