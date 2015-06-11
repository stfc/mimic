<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

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
$notes = mysql_query("select name, note from notes");
if ($notes and mysql_num_rows($notes)) {
    while ($note = mysql_fetch_assoc($notes)) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Gathers states
$all_status = Array();
$status = mysql_query("select name, state, source from state");
if ($status and mysql_num_rows($status)) {
    while ($state = mysql_fetch_assoc($status)) {
        $all_status[$state['name']] = Array(
            'state' => $state['state'],
            'source' => $state['source'],
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
foreach ($all_clusters as $name => $cluster) {
    if (($cluster != "vm-nubes") or (array_key_exists($name, $personality) == true)) {
        $results[$cluster][$name] = Array();
        if (array_key_exists($name, $all_notes)) {
            $results[$cluster][$name]['note'] = $all_notes[$name];
        };
        if (array_key_exists($name, $all_status)) {
            $results[$cluster][$name]['status'] = $all_status[$name];
        };
    }
}

// Renders page
echo "<div class=\"cluster-container\">";
foreach ($results as $cluster_name => $cluster) {
    echo "<div class=\"cluster\"><h5>$cluster_name</h5>";
    foreach ($cluster as $node_name => $node) {

        // Node information
        $nodeInfo = "<h4>$node_name</h4>";

        if ($node['status']['source'] == true) {
            $nodeInfo .= "<p><b>Source:</b> ".$node['status']['source']."</p>";
        }

        if ($node['status']['state'] == true) {
            $nodeStatus = $node['status']['state'];
            $nodeInfo .= "<p><b>Status:</b> ".$nodeStatus."</p>";
        } else {
            $nodeStatus = "unknown";
        }

        if ($node['note'] == true) {
            $nodeNote = $node['note'];
            $nodeStatus .= ' note';
            $nodeInfo .= "<p><b>Note:</b> ".$node['note']."</p>";
        }
        $short = explode(".", $node_name);
        $short = $short[0];
        $ntup = nagios_state($short, $node_name, $nodeStatus);
        if ($ntup[1] == true) {
            $nodeStatus = $ntup[0];
            $nodeInfo .= '<p><b>Nagios:</b>'.$ntup[1].'</p>';
        }
        unset($ntup);

        // Renders node
        echo '<span id="n_'.$node_name.'" onclick="node(\''.$node_name.'\')" class="node '.$nodeStatus.'" title="'.htmlentities($nodeInfo).'"></span>';
    }
    echo "</div>\n";
}
echo "</div>\n";
