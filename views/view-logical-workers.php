<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);

function is_worker_node($var) {
    return ((strpos($var, "wn-") !== false) || (strpos($var, "hadoop-") !== false));
}

// Get hosts and models from aquilon
$url = "$AQUILON_URL/cgi-bin/report/report_host_model";
$jsondata = file_get_contents($url);
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$all_clusters = json_decode($jsondata, true);
$all_clusters = array_filter($all_clusters, "is_worker_node");
uksort($all_clusters, "strnatcmp");


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
$status = $SQL->query("select name, state, source from state where name not like '%.scarf.rl.ac.uk'");
if ($status and $status->num_rows) {
    while ($state = $status->fetch_assoc()) {
        $all_status[$state['name']] = Array(
            $state['state'] => $state['source'],
        );
    }
}

$personalities = get_aquilon_report($CONFIG, 'host_personality_branch');
$models = get_aquilon_report($CONFIG, 'report_host_model');

// Generates main array
$results = Array();
$results['config'] = $config;


foreach ($all_clusters as $name => $panels) {
    if (($panels !== "vm-nubes") and (array_key_exists($name, $personalities) === true)) {

        $group = '';
        $panel = $panels;
        $cluster = '';

        if (array_key_exists($name, $personalities)) {
            $cluster = $personalities[$name]['personality'];
        };

        $results[$group][$panel][$cluster][$name] = Array();
        if (array_key_exists($name, $all_notes)) {
            $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
        };
        if (nagios($name) !== Null) {
            $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
        };
        if (array_key_exists($name, $all_status)) {
            $results[$group][$panel][$cluster][$name]['status'] = $all_status[$name];
            unset($all_status[$name]);
        };
    }
}


// Render all leftover hosts from the batch state table
// this catches any hosts created via cloud-bursting
foreach ($all_status as $hostname => $status) {
    if ($hostname !== '') {
        $source = '';
        if (sizeof($status) > 0) {
            $source = array_values($status);
            $source = $source[0];
        }

        $model = $source;
        if (array_key_exists($hostname, $models)) {
            $model = $models[$hostname];
        };

        $personality = '∅';
        if (array_key_exists($hostname, $personalities)) {
            if (array_key_exists('personality', $personalities[$hostname])) {
                $personality = $personalities[$hostname]['personality'];
            };
        };

        $details = Array(
            'status' => $status,
        );

        if (array_key_exists($hostname, $all_notes)) {
            $details['note'] = $all_notes[$hostname];
        };
        if (nagios($hostname) !== Null) {
            $details['nagios'] = nagios($hostname);
        };

        if ($source !== "Slurm on SCARF") {
            $results[''][$model][$personality][$hostname] = $details;
        };
    };
};


// Returns built json
echo json_encode($results) ;
