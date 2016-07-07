<?php
require("header.php"); // Important includes

// Config
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch");
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$all_nodes = json_decode($jsondata, true);
uksort($all_nodes, "strnatcmp");

$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/branch_type_owner");
$branches = json_decode($jsondata, true);

// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Initialise all branches (even empty ones!)
$results = Array();
foreach ($branches as $branch) {

    $group = $branch['branch_type'];
    $panel = $branch['branch_owner'];
    $cluster = $branch['branch_name'];

    $results[$group][$panel][$cluster] = Array();
}

// Generates main array
foreach ($all_nodes as $name => $panels) {

    $group = $panels["branch_type"];
    $panel = $panels["branch_owner"];
    $cluster = $panels["branch_name"];

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_nodes)) {
        $results[$group][$panel][$cluster][$name] = $all_nodes[$name];
    };
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    };
    if (nagios($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
    };
}

foreach ($results as $group => $panel) {
    foreach ($panel as $owner => $nodes) {
        if ($group != "domain") {
            $realname = $owner;
            //Converts username to real name
            $username_lookup = $CONFIG['ID']['PATH'];
            if (file_exists($username_lookup)) {
                $realname = exec("$username_lookup $owner");
                $realname = explode(',', $realname);
                $realname = $realname[0];
                if (! $realname) {
                    $realname = $owner;
                }
            }
            if ($realname != $owner) {
                $results[$group][$realname] = $results[$group][$owner];
                unset($results[$group][$owner]);
            }
        } else {
            $results[$group]["Aquilon"] = $results[$group][$owner];
            unset($results[$group][$owner]);
        }
    }
}

ksort($results);
foreach ($results as $group => $panel) {
    asort($results[$group]);
}

// Returns built json
echo json_encode($results);
