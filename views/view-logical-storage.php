<?php
require("header.php"); // Important includes

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"machineName\" as \"machineName\", \"fqdn\" as \"name\", \"castorInstance\", \"diskPool\", \"dxtx\", \"currentStatus\" "
    ."from \"vCastorFQDN\" "
    ."where \"normalStatus\" not in ('Retired', 'Decomissioned') "
    ."order by \"castorInstance\", \"diskPool\", \"machineName\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'metacluster' => $row['castorInstance'],
            'cluster' => $row['diskPool'],
            'status' => $row['currentStatus'],
            'dxtx' => $row['dxtx'],
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

    $metacluster = $clusterinfo['metacluster'];
    $cluster =  $clusterinfo['cluster'];

    $results[$metacluster][$cluster][$name] = Array();

    $results[$metacluster][$cluster][$name]['status']['dxtx'] = $clusterinfo['dxtx'];
    $results[$metacluster][$cluster][$name]['status']['source'] = 'Overwatch';
    $results[$metacluster][$cluster][$name]['status']['state'] = $clusterinfo['status'];

    if (array_key_exists($name, $all_notes)) {
        $results[$metacluster][$cluster][$name]['note'] = $all_notes[$name];
    };
}

// Renders page
echo "<div class=\"cluster-container\">";
foreach ($results as $m_cluster_name => $cluster) {
    echo "<div id=\"$m_cluster_name\" class=\"cluster\">";
    echo "<h3 id=\"$m_cluster_name\">$m_cluster_name</h3>";
    foreach ($cluster as $diskpool => $value) {
        echo "<h5 class=\"diskpool\">$diskpool</h5>";
        foreach ($value as $node_name => $node) {

        // Node information
            $nodeInfo = "<h4>$node_name</h4>";

            // Shows dxtx
            $nodeInfo .= "<p><b>dxtx:</b> ".$node['status']['dxtx']."</p>";

            // Shows source
            if ($node['status']['source'] == true) {
                $nodeInfo .= "<p><b>Source:</b> ".$node['status']['source']."</p>";
            }

            // Shows node status
            $nodeStatus = "unknown"; // Default
            if ($node['status']['state'] == true) {
                $nodeStatus = $node['status']['state'];
                $nodeInfo .= "<p><b>Status:</b> ".$nodeStatus."</p>";
            }

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
    }
    echo "</div>\n";
}
echo "</div>\n";
