<?php
require("header.php"); // Important includes
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);

// Gathers clusters
$all_clusters = Array();
$data = pg_query('select "name", "categoryName" from "vBatchAndCloudVMs"');
if ($data and pg_num_rows($data)){
    while ($row = pg_fetch_assoc($data)) {
        $all_clusters[$row['name']] = $row['categoryName'];
    }
}

// Gathers notes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Gathers states
$all_status = Array();
$status = $SQL->query("select name, state, source from state");
if ($status and $status->num_rows) {
    while ($state = $status->fetch_assoc()) {
        $all_status[$state['name']] = Array(
            $state['state'] => $state['source'],
        );
    }
}

// Gathers VM node if a worker
$personality = Array();
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch_nubes");
$vms = json_decode($jsondata, true);
foreach ($vms as $name => $values) {
    foreach ($values as $key => $value) {
        if (strpos($value, "workernode") !== false) {
            $personality[$name][$value] = $name;
        }
    }
}

// Generates main array
$results = Array();
$results['config'] = $config;
foreach ($all_clusters as $name => $panels) {
    if (($panels !== "vm-nubes") or (array_key_exists($name, $personality) === true)) {

        $group = '';
        $panel = $panels;
        $cluster = '';

        $results[$group][$panel][$cluster][$name] = Array();
        if (array_key_exists($name, $all_notes)) {
            $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
        };
        if (nagios($name) !== Null) {
            $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
        };
        if (array_key_exists($name, $all_status)) {
            $results[$group][$panel][$cluster][$name]['status'] = $all_status[$name];
        };
    }
}

// Returns built json
echo json_encode($results) ;
