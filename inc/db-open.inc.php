<?php

  $DB_GEN_HOST      = $CONFIG['DB_GEN']['HOST'];
  $DB_GEN_USER      = $CONFIG['DB_GEN']['USER'];
  $DB_GEN_PASS      = $CONFIG['DB_GEN']['PASS'];

  $DB_BATCH_NAME    = $CONFIG['DB_GEN']['BATCH_NAME'];

  $DB_GEN_HANDLE = mysql_pconnect($DB_GEN_HOST, $DB_GEN_USER, $DB_GEN_PASS)
    or trigger_error("Unable to open general database connection", E_USER_ERROR);

  $DB_BATCH_HANDLE  = $DB_GEN_HANDLE;

  mysql_select_db($DB_BATCH_NAME, $DB_BATCH_HANDLE)
    or trigger_error("Cannot use $DB_BATCH_NAME", E_USER_ERROR);
?>
