<?php
require("header.php"); // Important includes
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"fqdn\" as \"name\", \"machineName\" as \"short\", \"currentStatus\", \"hardwareGroup\", \"castorInstance\" "
    ."from \"vCastorFQDN\" "
    ."order by \"hardwareGroup\", \"machineName\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'panel' => $row['hardwareGroup'],
            'state' => $row['currentStatus'],
            );
    }
}

// Gathers notes
$all_notes = Array();
$notes = mysql_query("select name, note from notes");
if ($notes and mysql_num_rows($notes)) {
    while ($note = mysql_fetch_assoc($notes)) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
generational_results($all_clusters, $all_notes);
include_once("inc/error.inc.php");
