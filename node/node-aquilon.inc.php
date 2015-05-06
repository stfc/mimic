<?php
class pAquilon {
  function header($NODE, $SHORT) {
    return(Array("Quattor (Aquilon)"));
  }
  function detail($NODE, $SHORT) {
    global $CONFIG;
?>
<div id="aquilonDetails">
 <?php
 $info = False;
 $url = $CONFIG['AQUILON']['URL'] . "host/".urlencode($NODE);

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
       echo "<li><strong>$key</strong> &ndash; $val";
     }
     $p_in = $in;
   }
   echo "</ul>\n";
 }
 else {
   echo "<p class=\"info\">No info for host.</p>\n";
 }
 ?>
</div>
<?php
    }
  }
  return new pAquilon();
?>
