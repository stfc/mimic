<?php

// Routine for displaying the timestamps
function prettytime ($timedelta) {
  $prefix = "";
  $suffix = " ago";

  if ($timedelta < 0) {
    $prefix = "In ";
    $suffix = "";
    $timedelta = $timedelta * -1;
  }

  if ($timedelta < 120) { return sprintf("$prefix%.1f seconds$suffix", $timedelta); }
  elseif ($timedelta < 120*60) { return sprintf("$prefix%.1f minutes$suffix", $timedelta/60); }
  elseif ($timedelta < 24*60*60) { return sprintf("$prefix%.1f hours$suffix", $timedelta/(60*60)); }
  elseif ($timedelta < 24*60*60*7) { return sprintf("$prefix%.1f days$suffix", $timedelta/(24*60*60)); }
  elseif ($timedelta < 24*60*60*365) { return sprintf("$prefix%.1f weeks$suffix", $timedelta/(24*60*60*7)); }
  else { return sprintf("$prefix%.1f years$suffix", $timedelta/(24*60*60*365)); }
}

function fPluginFail($errno, $errstr, $errfile, $errline) {
  echo "  <div><p>Parse error while loading plugin. $errstr $errfile $errline</p></div>\n";
}
