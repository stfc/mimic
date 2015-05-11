<?php
$path = '/var/www/html/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

$CONFIG = parse_ini_file("config/config.ini", true); // Config file

require("inc/db-open.inc.php"); // MySQL Data sources
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library
?>
