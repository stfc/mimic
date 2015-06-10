<?php
require("header.php"); // Important includes

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"systemHostname\" as \"name\", \"categoryName\", \"rackId\", \"systemRackPos\" "
    ."from \"vBuildTemplate\" "
    ."where \"systemHostname\" not like '%.internal'"
    ."order by \"categoryName\" desc, \"systemHostname\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'cluster' => $row['categoryName'],
            );
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

// Generates main array
$results = Array();
foreach ($all_clusters as $name => $clusterinfo) {
    $cluster =  $clusterinfo['cluster'];
    $results[$cluster][$name] = Array();
    if (array_key_exists($name, $all_notes)) {
        $results[$cluster][$name]['note'] = $all_notes[$name];
    };
}

// Renders page
echo "<div class=\"cluster-container\">";
foreach ($results as $m_cluster_name => $cluster) {
    echo "<div id=\"$m_cluster_name\" class=\"cluster\">";
    echo "<h5 id=\"$m_cluster_name\">$m_cluster_name</h5>";
        foreach ($cluster as $node_name => $node) {

        // Node information
            $nodeInfo = "<h4>$node_name</h4>";

            // Shows node status
            $nodeStatus = "unknown"; // Default
            // Shows node note
            if ($node['note'] == true) {
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
