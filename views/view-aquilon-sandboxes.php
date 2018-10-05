<?php
require("header.php"); // Important includes

// Configuration
$config = Array(
    "clickable" => true,
);

// branch and personality for nodes
$all_nodes = get_aquilon_report($CONFIG, 'host_personality_branch');

// owner and type for branches
$branches = get_aquilon_report($CONFIG, 'branch_type_owner');

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
$results['config'] = $config;
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
    }
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    }
    if (nagios($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
    }
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
            foreach ($results[$group][$owner] as $key => $value) {
                $results[$group]["Aquilon"][$key] = $value;
            }
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
