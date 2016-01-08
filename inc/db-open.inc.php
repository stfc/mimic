<?php

$DB_GEN_HOST      = $CONFIG['DB_GEN']['HOST'];
$DB_GEN_USER      = $CONFIG['DB_GEN']['USER'];
$DB_GEN_PASS      = $CONFIG['DB_GEN']['PASS'];

$DB_BATCH_NAME    = $CONFIG['DB_GEN']['BATCH_NAME'];

$DB_GEN_HANDLE = mysql_pconnect($DB_GEN_HOST, $DB_GEN_USER, $DB_GEN_PASS)
or error("Unable to connect to", "nagiosdb host");

$DB_BATCH_HANDLE  = $DB_GEN_HANDLE;

mysql_select_db($DB_BATCH_NAME, $DB_BATCH_HANDLE)
or error("Cannot set active database:", $DB_BATCH_NAME);
