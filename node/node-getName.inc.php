<?php
// Check the node
$NODE = filter_input(INPUT_GET, 'n', FILTER_SANITIZE_STRING);
if ($NODE === Null){
    $NODE = "[Unknown]";
}
$NODE = preg_replace("/[^a-zA-Z0-9.-]/", "", $NODE); #strip out all but valid hostname characters
$SHORT = $NODE;
if (substr($NODE, -3, 3) == ".uk") {
    list($SHORT,$rest) = explode(".", $NODE, 2);
}
