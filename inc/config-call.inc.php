<?php

if (file_exists("{$path}config/user-config.ini")) {
    $CONFIG = parse_ini_file("{$path}config/user-config.ini", true);
//    printf("<!-- User config detected. Using user config -->"."\n");
} else {
    $CONFIG = parse_ini_file("{$path}config/default-config.ini", true);
//    printf("<!-- User config not found! Using default -->"."\n");
};

?>
