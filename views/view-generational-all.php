<?php
require("header.php"); // Important includes

// Go find all our nodes
$instance = '';
$cluster = '';
$num = 0;
$allnodes = pg_query(
    "select \"systemHostname\" as \"name\", \"systemHostname\" as \"short\", \"categoryName\", \"rackId\", \"systemRackPos\" "
    ."from \"vBuildTemplate\" "
    ."where \"systemHostname\" not like '%.internal'"
    ."order by \"categoryName\" desc, \"systemHostname\";"
);

if ($allnodes and pg_num_rows($allnodes)){
    while ($r = pg_fetch_row($allnodes)) {
        /* Start of main loop... */
        // We're looking at this node
        $node = $r[0];
        $short = explode(".", $node);
        $short = $short[0];

        // In this cluster
        if ($r[2] != $cluster) {
            if ($cluster != '') {
                echo "        </div>\n";
            }
            $cluster = $r[2];
            $s_cluster = str_replace("/", "", $cluster);

            echo "<div class=\"cluster\" id=\"cl_$s_cluster\">\n";
            echo "<h5 class=\"cluster\">$s_cluster</h5>\n";
        }

        // Set defaults
        $nodeInfo = "";
        $nodeStatus = "unknown";
        $nodeNote = "";

        // Batch & Notes
        $mynode = mysql_query("select state, note from state LEFT JOIN notes on (notes.name=state.name) where state.name='$node';");
        if ($mynode and mysql_num_rows($mynode)) {
            $mn_r = mysql_fetch_row($mynode);
            $nodeStatus = $mn_r[0];
            $nodeNote = $mn_r[1];
        }

        if (!$nodeStatus) {
            $nodeStatus = "unknown";
        }

        if ($nodeStatus == "down") {
            $nodeStatus = "batchdown";
        }

        $nodeInfo = "<h4>$node</h4> ($nodeStatus - Torque)";

        // Add note flag
        if ($nodeNote) {
            $nodeStatus .= ' note';
        }

        $ntup = nagios_state($short, $node, $nodeStatus);
        if ($ntup[1]) {
            $nodeStatus = $ntup[0];
            $nodeInfo .= "<p><b>Nagios:</b> {$ntup[1]}</p>";
        }
        unset($ntup);

        // And show it
        echo '<span id="n_'.$short.'" onclick="node(\''.$node."')\" class=\"node $nodeStatus\" title=\"".htmlentities($nodeInfo).'"></span>'."\n";
    }
    echo "</div>\n";
    echo "</div>\n";
}

?>
