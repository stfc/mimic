<?php

$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (file_exists("{$path}config/user-config.ini")) {
    $CONFIG = parse_ini_file("{$path}config/user-config.ini", true);
} else {
    $CONFIG = parse_ini_file("{$path}config/default-config.ini", true);
};
