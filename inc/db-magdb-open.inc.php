<?php

  $PG_HOST = $CONFIG['DB_PG']['HOST'];
  $PG_USER = $CONFIG['DB_PG']['USER'];
  $PG_PASS = $CONFIG['DB_PG']['PASS'];
  $PG_NAME = $CONFIG['DB_PG']['NAME'];

  #Connect to DB
  $PGLINK = pg_connect("host=$PG_HOST user=$PG_USER password=$PG_PASS dbname=$PG_NAME")
    or trigger_error("Unable to open connection to magDB", E_USER_ERROR);

  pg_query("SET application_name = 'mimic-web';");
?>
