<?php
// Check the node
$NODE = (isset($_REQUEST["n"])) ? $_REQUEST["n"] : "[unknown]";
$NODE = preg_replace("/[^a-zA-Z0-9.-]/", "", $NODE); #strip out all but valid hostname characters
$SHORT = $NODE;
if (substr($NODE, -3, 3) == ".uk") {
  list($SHORT,$rest) = explode(".", $NODE, 2);
}
