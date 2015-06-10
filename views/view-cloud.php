<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch_nubes");
$all_nodes = json_decode($jsondata, true);
ksort ($all_nodes);

// Gets notes for nodes
$all_notes = Array();
$notes = mysql_query("select name, note from notes");
if ($notes and mysql_num_rows($notes)) {
    while ($note = mysql_fetch_assoc($notes)) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Separates nodes to their correct sections
$cluster = Array();
foreach ($all_nodes as $name => $values) {
    $type = "infrastructure";
    if (strpos($name, "vm") !== false) {
       $type = "vm";
    }
    $cluster[$type][$values["personality"]][] = $name;
}

// Loops through for each cluster of nodes
foreach ($cluster as $section_title => $values) {
    echo "<div class='cluster-container'>";
    echo "<h2 class='$section_title'>$section_title</h2>\n";
    foreach ($values as $c_name => $key) {
        echo "<div class='cluster'><h5>$c_name</h5>";
        foreach ($key as $node_name) {

            $nodeInfo = "<h4>$node_name</h4>";

            // Shows node status
            $nodeStatus = "unknown";
            if ($section_title == "vm") {
                if (($all_nodes[$node_name]["branch_name"] != "prod") or ($all_nodes[$node_name]["personality"] != "nubesvms")) {
                    $nodeStatus = 'cloud-bad';
                    $nodeInfo .= "<p><b>Warning:</b> VM host not in prod or personality not nubesvms, will need to be cleaned up after deletion.</p>";
                }
            }

            // Shows node note
            if ($all_notes[$node_name] == true) {
                $nodeNote = $node['note'];
                $nodeStatus .= ' note';
                $nodeInfo .= "<p><b>Note:</b> ".$node['note']."</p>";
            }

            // Shows nagios state
            $short = explode(".", $node_name);
            $short = $short[0];
            $ntup = nagios_state($short, $node_name, $nodeStatus);
            if ($ntup[1] == true) {
                $nodeStatus .= ' '.$ntup[0];
                $nodeInfo .= '<p><b>Nagios:</b>'.$ntup[1].'</p>';
            }
            unset($ntup);

            // Renders node
            echo '<span id="n_'.$node_name.'" onclick="node(\''.$node_name.'\')" class="node '.$nodeStatus.'" title="'.htmlentities($nodeInfo).'"></span>';
        }
        echo "</div>\n";
    }
    echo "</div>\n";
}

?>
