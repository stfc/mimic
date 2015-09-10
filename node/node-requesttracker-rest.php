<?php
$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
if ($name !== Null) {
    $name = preg_replace("/[^a-zA-Z0-9_-]/", "", $name);
    echo exec("/usr/bin/python node-requesttracker-rest.py $name history");
}
else {
    echo "{}";
}
