<?php
require("header.php"); // Important includes

// Config
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch");
$data = json_decode($jsondata, true);
ksort ($data);
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
foreach ($data as $name => $values) {

    // Gathers nodes into the top level groups
    $archetype = $values["archetype"];

    if ($archetype === "rig") {
        $group_name = "rig";
    } elseif ($archetype === "rig_unmanaged") {
        $group_name = "rig_unmanaged";
    } elseif ($archetype === "isis") {
        $group_name = "isis";
    } elseif ($archetype === "ral-tier1") {
        $group_name = "ral-tier1";
    } else {
        $group_name = "unknown";
    }

    // Gathers nodes into second clusters
    $cluster[$group_name][$values["personality"]][] = $name;
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
