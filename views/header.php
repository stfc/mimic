<?php

$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require("inc/config-call.inc.php");
require("inc/db-open.inc.php"); // MySQL Data sources
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library

function display($results) {

    foreach ($results as $group_name => $group) {
        echo "<div class='node-group' id='$group_name'>";
        if ($group_name == true) {
            echo "<h2 class='group-name'>$group_name</h2>";
        }

        foreach ($group as $panel_name => $panel) {
            echo "<div class='node-panel' id='$panel_name'>";
            echo "<h3 class='panel-name'>$panel_name</h3>";
            foreach ($panel as $cluster_name => $cluster) {
                echo "<div class='node-cluster' id='$cluster_name'>";
                if ($cluster_name == true) {
                    echo "<h5 class='cluster-name'>$cluster_name</h5>";
                };

                if (!empty($cluster)) {
                    foreach ($cluster as $node_name => $node) {
                        // Node information
                        $nodeInfo = "<h4>$node_name</h4>";
                        $nodeStatus = "";

                        $nodeStatus = "unknown";
                        if (array_key_exists('status', $node)) {
                            if (array_key_exists('state', $node['status'])) {
                                if ($node['status']['state'] == true) {
                                    $nodeStatus = $node['status']['state'];
                                }
                            }
                        }
                        if (array_key_exists('note', $node)) {
                            if ($node['note'] == true) {
                                $nodeNote = $node['note'];
                                $nodeStatus .= ' note';
                                $nodeInfo .= "<p><b>Note:</b> ".$node['note']."</p>";
                            }
                        }
                        $short = explode(".", $node_name);
                        $short = $short[0];
                        $ntup = nagios_state($short, $node_name, $nodeStatus);
                        if ($ntup[1] == true) {
                            $nodeStatus = $ntup[0];
                            $nodeInfo .= '<p><b>Nagios:</b>'.$ntup[1].'</p>';
                        }
                        unset($ntup);

                        foreach($node as $key => $value) {
                            if (is_array($value)) {
                                foreach($value as $k => $v) {
                                    if ( $k != 'panel' and $k != 'cluster') {
                                        $nodeInfo .= "<p><b>$k</b>: $v</p>\n";
                                    }
                                }
                            }
                        }

                        // Renders node
                        echo '<span id="n_'.$node_name.'" onclick="node(\''.$node_name.'\')" class="node '.$nodeStatus.'" title="'.htmlentities($nodeInfo).'"></span>';
                    }
                } else {
                    // Renders no node
                    echo "<span title='No managed systems'>&nbsp;&#x2205;</span>";
                }
                echo "</div>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
}
