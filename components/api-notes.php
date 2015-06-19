<?php
$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require('inc/config-call.inc.php');
require('inc/functions.inc.php');
require('inc/db-open.inc.php');


$redo = 0;
$NOTE = (isset($_REQUEST["note"])) ? $_REQUEST["note"] : Null;
$NODE = (isset($_REQUEST["node"])) ? $_REQUEST["node"] : "";

$result = Array(
    "status" => "fail",
    "time"   => prettytime(0),
    "note"   => "",
);

if ($NODE) {
    if ($NOTE !== Null) {
        $redo = 1;
        if (preg_match("/^\s*$/", $NOTE)) {
            // delete note if empty
            $done = mysql_query("delete from notes where name='".
            mysql_escape_string($NODE)."'");
        } else {
            // insert/update note
            $NOTE = preg_replace(
                array ("/\s\s+/", "/^\s*(.*?)\s*$/"),
                array (" ", "$1"),
                $NOTE
            );
            $done = mysql_query("update notes set note='".mysql_escape_string($NOTE)."', time=null where name='".mysql_escape_string($NODE)."'");

            if (mysql_affected_rows()==0) {
                $done = mysql_query("insert into notes (name, note, time) values ('".mysql_escape_string($NODE)."', '".mysql_escape_string($NOTE)."', null)");
            }
        }
        if (mysql_affected_rows()==1) {
            $result["status"] = "ok";
            $result["note"] = mysql_escape_string($NOTE);
        }
    }
    else {
        $got = mysql_query("select note, unix_timestamp() - unix_timestamp(time) from notes where name='".mysql_escape_string($NODE)."'");
        if ($got and mysql_num_rows($got)) {
            $r = mysql_fetch_row($got);
            $result["note"] = htmlspecialchars($r[0]);
            $result["time"] = prettytime($r[1]);
        }
        $result["status"] = "ok";
    }
}

echo json_encode($result);

?>
