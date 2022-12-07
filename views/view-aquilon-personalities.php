<?php
require("header.php"); // Important includes

// Configuration
$config = Array(
    "clickable" => true,
);

// branch and personality for nodes
$all_nodes = get_aquilon_report($CONFIG, 'host_personality_branch');

// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
$results = Array();
$results['config'] = $config;
foreach ($all_nodes as $name => $panels) {

    $archetype = $panels["archetype"];
    $group = "unknown";
    $panel = $panels["personality"];
    $cluster = '';

    if ($archetype === "rig") {
        $group = "rig";
    } elseif ($archetype === "rig_unmanaged") {
        $group = "rig_unmanaged";
    } elseif ($archetype === "isis") {
        $group = "isis";
    } elseif ($archetype === "ral-tier1") {
        $group = "ral-tier1";
    } elseif ($archetype === "ral-tier1-unmanaged") {
        $group = "ral-tier1-unmanaged";
    } elseif ($archetype === "ral-tier1-minimal") {
        $group = "ral-tier1-minimal";
    }
    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_nodes)) {
        $results[$group][$panel][$cluster][$name] = $all_nodes[$name];
    }
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    }
    if (mklivestatus_oneline($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['mklivestatus'] = mklivestatus_oneline($name);
    }
}

// Returns built json
echo json_encode($results);
