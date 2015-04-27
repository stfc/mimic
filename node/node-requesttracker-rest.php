<?php
    if (isset($_REQUEST["name"])) {
        $name = $_REQUEST["name"];
        $name = preg_replace("/[^a-zA-Z0-9_-]/", "", $name); 
        echo exec("/usr/bin/python node-requesttracker-rest.py $name history");
    }
    else {
        echo "{}";
    }
?>
