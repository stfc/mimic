<?php

require('inc/config-call.inc.php');
require('inc/functions.inc.php');
require('inc/db-open.inc.php');

$redo = 0;
$NOTE = filter_input(INPUT_GET, 'note', FILTER_SANITIZE_STRING);
$NODE = filter_input(INPUT_GET, 'node', FILTER_SANITIZE_STRING);

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
            $done = $SQL->query(sprintf("delete from notes where name='%s'", $SQL->real_escape_string($NODE)));
            if ($done) {
                $result["status"] = "ok";
            }
        } else {
            // insert/update note
            $NOTE = preg_replace(
                array ("/\s\s+/", "/^\s*(.*?)\s*$/"),
                array (" ", "$1"),
                $NOTE
            );
            $done = $SQL->query(sprintf("insert into notes (name, note, time) values ('%s', '%s', null) ON DUPLICATE KEY UPDATE note=VALUES(note)", $SQL->real_escape_string($NODE), $SQL->real_escape_string($NOTE)));
            if ($done) {
                $result["status"] = "ok";
                $result["note"] = $SQL->real_escape_string($NOTE);
            }
        }
    }
    else {
        $got = $SQL->query("select note, unix_timestamp() - unix_timestamp(time) from notes where name='".$SQL->real_escape_string($NODE)."'");
        if ($got and $got->num_rows) {
            $row = $got->fetch_row();
            $result["note"] = htmlspecialchars($row[0]);
            $result["time"] = prettytime($row[1]);
        }
        $result["status"] = "ok";
    }
}

echo json_encode($result);
