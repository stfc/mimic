<?php
require("header.php"); // Important includes
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources

// Configuration
$config = Array(
    "clickable" => true,
);

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"fqdn\" as \"name\", \"machineName\" as \"short\", \"currentStatus\", \"hardwareGroup\", \"castorInstance\" "
    ."from \"vCastorFQDN\" "
    ."order by \"hardwareGroup\", \"machineName\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'panel' => $row['hardwareGroup'],
        );
        $status[$row['name']] = Array(
            $row['currentStatus'] => 'Overwatch',
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
$results['config'] = $config;
foreach ($all_clusters as $name => $panels) {
    $group = '';
    $panel = $panels['panel'];
    $cluster = '';

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    };
    if (nagios($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
    };
    if (array_key_exists($name, $status)) {
        $results[$group][$panel][$cluster][$name]['status'] = $status[$name];
    };
}

// Returns built json
echo json_encode($results);
