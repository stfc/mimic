<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);

$REPORTS = Array(
    "" => "$AQUILON_URL/cgi-bin/report/report_cluster_hosts",
);

// Generates main array
$results = Array();
$results['config'] = $config;

// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

foreach ($REPORTS as $type => $url) {
    $jsondata = file_get_contents($url);
    if ($jsondata === false) {
        error("No data returned from", "aquilon");
    }
    $all_nodes = json_decode($jsondata, true);
    uksort($all_nodes, "strnatcmp");

    foreach ($all_nodes as $name => $node) {
        $group = $node["cluster_archetype"];
        $panel = $node["cluster_personality"];
        $cluster = $node["cluster_name"];

        // Use name from record if provided
        if (array_key_exists("fqdn", $node)) {
            $name = $node["fqdn"];
            unset($node["fqdn"]);
        }

        $results[$group][$panel][$cluster][$name] = $node;

        if (array_key_exists($name, $all_notes)) {
            $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
        }
        if (nagios($name) !== Null) {
            $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
        }
    }
}

// Returns built json
echo json_encode($results);
