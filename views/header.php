<?php

$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require("inc/config-call.inc.php");
require("inc/db-open.inc.php"); // MySQL Data sources
require("inc/db-magdb-open.inc.php"); // Postgres Data Sources
require("inc/main-nagios.inc.php"); // Nagios library

function display($results) {

    foreach ($results as $group_name => $group) {

        if (!empty($group_name)) {
            echo "<h2 class='group-name'>$group_name</h2>";
        }
        echo "<div class='node-group' id='$group_name'>";
        foreach ($group as $panel_name => $panel) {

            echo "<div class='node-panel grid-item' id='$panel_name'>";
            echo "<h3 class='panel-name'>$panel_name</h3>";
            foreach ($panel as $cluster_name => $cluster) {
                echo "<div class='node-cluster' id='$cluster_name'>";
                if (!empty($cluster_name)) {
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
                                if (!empty($node['status']['state'])) {
                                    $nodeStatus = $node['status']['state'];
                                }
                            }

                            // VMs only - Node shows as critical if conditions are met
                            if (strpos($node_name, "vm") !== false) {
                                if ($node['status']['state'] == "uninstantiated") {
                                    if ($node["branch_name"] != "prod") {
                                        $nodeStatus .= ' critical';
                                        $nodeInfo .= "<p><b>Aquilon:</b> Warning: Uninstantiated VM not in prod, needs to be cleaned up!</p>";
                                    }
                                    if ($node["personality"] != "nubesvms") {
                                        $nodeStatus .= ' critical';
                                        $nodeInfo .= "<p><b>Aquilon:</b> Warning: Uninstantiated VM personality not nubesvms, needs to be cleaned up!</p>";
                                    }
                                }
                                elseif ($node["branch_name"] != "prod") {
                                    $nodeStatus .= ' warning';
                                    $nodeInfo .= "<p><b>Aquilon:</b> Warning: VM host not in prod</p>";
                                }
                            }
                        }
                        if (array_key_exists('note', $node)) {
                            if (!empty($node['note'])) {
                                $nodeNote = $node['note'];
                                $nodeStatus .= ' note';
                                $nodeInfo .= "<p><b>Note:</b> ".$node['note']."</p>";
                            }
                        }
                        $short = explode(".", $node_name);
                        $short = $short[0];
                        $ntup = nagios_state($short, $node_name, $nodeStatus);
                        if (!empty($ntup[1])) {
                            $nodeStatus = $ntup[0];
                            $nodeInfo .= '<p><b>Nagios:</b>'.$ntup[1].'</p>';
                        }
                        unset($ntup);

                        foreach($node as $key => $value) {
                            if (is_array($value)) {
                                foreach($value as $k => $v) {
                                    if ( $k != 'panel' and $k != 'cluster') {
                                        $nodeInfo .= "<p><b class='info-type'>$k</b>: $v</p>\n";
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
