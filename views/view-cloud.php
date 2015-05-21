<?php
require("header.php"); // Important includes

// Config
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch_nubes");
$allnodes = json_decode($jsondata, true);
ksort ($allnodes);

// Loop for all nodes
function do_node($node, $section) {
    global $allnodes;
    global $mynode;

    $short = explode('.', $node);
    $short = $short[0];

    $mynode  = mysql_query("select note from nodelist LEFT JOIN notes on (notes.name=nodelist.name) where name=$short ORDER BY layer;");
    if ($mynode and mysql_num_rows($mynode)) {
        $nodecsf = mysql_fetch_row($mynode);
    }
    else {
        $nodecsf = null;
    }

    $nodeNote = "";
    $nodeInfo = "<h4>$node</h4>";

    // Set defaults
    $nodeStatus = "unknown";
    if ($section == "vm") {
        if (($allnodes[$node]["branch_name"] != "prod") or ($allnodes[$node]["personality"] != "nubesvms")) {
            $nodeStatus = 'cloud-bad';
            $nodeInfo .= "<p><b>Warning:</b> VM host not in prod or personality not nubesvms, will need to be cleaned up after deletion.</p>";
        }
    }

    $nodeNote = $nodecsf[0];
    $ntup = nagios_state($short, $node, $nodeStatus);
    if ($ntup[1]) {
        $nodeStatus = $ntup[0];
        $nodeInfo .= "<p><b>Nagios:</b> {$ntup[1]}</p>";
    }
    unset($ntup);

    // Process notes
    if ($nodeNote != "") {
        // Tack note onto end of info string
        $nodeInfo .= ' - '.$nodeNote;

        // We want to be case insensitive!
        $s = strtolower($nodeNote);
        $nodeStatus .= ' note';
    }

    // Makes the node display
    echo '<span id="n_'.$short.'" onclick="node(\''.$node.'\')" class="node '.$nodeStatus.'" title="'.htmlentities($nodeInfo).'"></span>';
}

// Separates nodes to their correct sections
$cluster = Array();
foreach ($allnodes as $name => $values) {
    $type = "infrastructure";
    if (strpos($name, "vm") !== false) {
       $type = "vm";
    }
    $cluster[$type][$values["personality"]][] = $name;
}

// Loops through for each cluster of nodes
function do_clusters($section){

    global $allnodes;
    global $cluster;

    foreach ($cluster as $section_title => $values) {
        echo "<div class=\"cluster-container\">";
        echo "<h2 class=\"$section_title\">$section_title</h2>\n";
        foreach ($values as $c_name => $key) {
            echo "<div class=\"cluster\"><h5>$c_name</h5>";
            foreach ($key as $f) {
                do_node($f, $section_title);
            }
            echo "</div>\n";
        }
        echo "</div>\n";
    }
}
// Shows content on page
do_clusters($cluster);
?>
