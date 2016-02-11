<?php
require("inc/config-call.inc.php");
require("inc/functions.inc.php");
header('Content-Type: application/json');

function bool2str($value) {
    // PHP is pretty bad at representing booleans in a human readable way so we'll do it ourselves
    if ($value === true) {
        $value = "true";
    } elseif ($value === false) {
        $value = "false";
    }
    return($value);
}
