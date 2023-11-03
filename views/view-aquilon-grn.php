<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);

// branch and personality for nodes
$url = "$AQUILON_URL/cgi-bin/report/host_grn_personality";
if ($user) {
    $url .= "?owner=$user";
};
$jsondata = file_get_contents($url);
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$all_nodes = json_decode($jsondata, true);
uksort($all_nodes, "strnatcmp");


// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
foreach ($all_nodes as $name => $panels) {

    $grn = Array();

    preg_match('/^([^<>]+)(?: <(.+)>)?$/m', $panels["grn"], $grn);

    $group = htmlentities($grn[1]);

    if (sizeof($grn) == 3) {
        $panels["email"] = htmlentities($grn[2]);
    };

    $panel = htmlentities($panels["personality"]);
    $cluster = '';

    $panels["grn"] = htmlentities($panels["grn"]);

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_nodes)) {
        $results[$group][$panel][$cluster][$name] = $panels;
    }
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    }
    if (mklivestatus_oneline($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['mklivestatus'] = mklivestatus_oneline($name);
    }
}

ksort($results);
foreach ($results as $group => $panel) {
    asort($results[$group]);
}

// Returns built json
echo json_encode($results);
