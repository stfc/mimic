<?php
$path = '/var/www/html/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require("inc/config-call.inc.php");
require("inc/db-open.inc.php"); // MySQL Data sources
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library
?>
