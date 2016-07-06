<?php
require("header.php"); // Important includes
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"systemHostname\" as \"name\", \"categoryName\", \"rackId\", \"systemRackPos\" "
    ."from \"vBuildTemplate\" "
    ."where \"systemHostname\" not like '%.internal'"
    ."order by \"categoryName\", \"systemHostname\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'panel' => $row['categoryName'],
        );
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

// Generates main array
$results = Array();
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
}

// Returns built json
echo json_encode($results);
