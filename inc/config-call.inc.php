<?php

$root = "/var/www/html/";
if (file_exists("{$root}config/user-config.ini")) {
    $CONFIG = parse_ini_file("{$root}config/user-config.ini", true);
    // printf("<!-- User config detected. Using user config -->"."\n");
} else {
    $CONFIG = parse_ini_file("{$root}config/default-config.ini", true);
    // printf("<!-- User config not found! Using default -->"."\n");
};

?>
