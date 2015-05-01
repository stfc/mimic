<?php
$path = '/var/www/html/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

  # MySQL Data sources
require("inc/db-open.inc.php");

  # Postgres Data Sources
require("inc/db-magdb-open.inc.php");

  # Nagios library
require("inc/main-nagios.inc.php");
?>