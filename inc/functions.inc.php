<?php

// Routine for displaying the timestamps
function prettytime ($t) {
  $prefix = "";
  $suffix = " ago";

  if ($t < 0) {
    $prefix = "In ";
    $suffix = "";
    $t = $t * -1;
  }

  if ($t < 120) { return sprintf("$prefix%.1f seconds$suffix", $t); }
  elseif ($t < 120*60) { return sprintf("$prefix%.1f minutes$suffix", $t/60); }
  elseif ($t < 24*60*60) { return sprintf("$prefix%.1f hours$suffix", $t/(60*60)); }
  elseif ($t < 24*60*60*7) { return sprintf("$prefix%.1f days$suffix", $t/(24*60*60)); }
  elseif ($t < 24*60*60*365) { return sprintf("$prefix%.1f weeks$suffix", $t/(24*60*60*7)); }
  else { return sprintf("$prefix%.1f years$suffix", $t/(24*60*60*365)); }
}

function fPluginFail($errno, $errstr, $errfile, $errline) {
  echo "  <div><p>Parse error while loading plugin. $errstr $errfile $errline</p></div>\n";
}
