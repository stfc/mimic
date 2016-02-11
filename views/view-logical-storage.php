<?php
require("header.php"); // Important includes
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"fqdn\" as \"name\", \"castorInstance\", \"diskPool\", \"dxtx\", \"currentStatus\" "
    ."from \"vCastorFQDN\" "
    ."where \"normalStatus\" not in ('Retired', 'Decomissioned') "
    ."order by \"castorInstance\", \"diskPool\", \"machineName\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'panel' => $row['castorInstance'],
            'cluster' => $row['diskPool'],
            'state' => $row['currentStatus'],
            'dxtx' => $row['dxtx'],
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
$results = Array();
foreach ($all_clusters as $name => $panels) {

    $group = '';
    $panel = $panels['panel'];
    $cluster = $panels['cluster'];

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    };
    if (array_key_exists($name, $all_clusters)) {
        $results[$group][$panel][$cluster][$name]['status'] = $all_clusters[$name];
    };
}

// Returns built json
echo json_encode($results);
