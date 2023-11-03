<?php
require("header.php"); // Important includes


function is_scarf_host($var) {
    return (strpos($var, "scarf") !== false);
}


// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);


// Get hosts and models from aquilon
$url = "$AQUILON_URL/cgi-bin/report/report_host_model";
$jsondata = file_get_contents($url);
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$all_nodes = json_decode($jsondata, true);
$all_nodes = array_filter($all_nodes, "is_scarf_host");
uksort($all_nodes, "strnatcmp");


// Get branch and personality from aquilon
$url = "$AQUILON_URL/cgi-bin/report/host_grn_personality";
$jsondata = file_get_contents($url);
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$host_grn_personality = json_decode($jsondata, true);
uksort($host_grn_personality, "strnatcmp");


// Gathers notes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes where name like '%.scarf.rl.ac.uk'");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}


// Gathers states
$all_status = Array();
$status = $SQL->query("select name, state, source from state where name like '%.scarf.rl.ac.uk'");
if ($status and $status->num_rows) {
    while ($state = $status->fetch_assoc()) {
        $all_status[$state['name']] = Array(
            $state['state'] => $state['source'],
        );
    }
}

// Generates main array
$results = Array();
$results['config'] = $config;

// All nodes from aquilon
foreach ($all_nodes as $name => $model) {
    $group = 'aquilon';
    $panel = $model;
    $cluster = '';

    if (array_key_exists($name, $host_grn_personality)) {
        $cluster = $host_grn_personality[$name]['personality'];
    }

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    }
    if (mklivestatus_oneline($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['mklivestatus'] = mklivestatus_oneline($name);
    }
    if (array_key_exists($name, $all_status)) {
        $results[$group][$panel][$cluster][$name]['status'] = $all_status[$name];
        unset($all_status[$name]);
    }
}

// Render all leftover hosts from the batch state table
// this catches any hosts created via cloud-bursting
foreach ($all_status as $hostname => $status) {
    if ($hostname !== '') {
        $group = '';
        $panel = 'dynamic';
        $source = '';
        if (sizeof($status) > 0) {
            $source = array_values($status);
            $source = $source[0];
        }
        $results[$group][$panel][$source][$hostname] = Array(
            'status' => $status,
        );
        if (array_key_exists($hostname, $all_notes)) {
            $results[$group][$panel][$source][$hostname]['note'] = $all_notes[$hostname];
        };
        if (mklivestatus_oneline($hostname, false) !== Null) {
            $results[$group][$panel][$source][$hostname]['mklivestatus'] = mklivestatus_oneline($hostname, false);
        };
    };
};


// Returns built json
echo json_encode($results) ;
