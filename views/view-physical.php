<?php
require("header.php"); // Important includes

// Go find all our nodes
$instance = '';
$room = '';
$rack = '';
$num = 0;
if (!isset($do_layout)) {
  $do_layout = true;
}

$sql = "select \"systemHostname\" as \"name\", \"roomName\", \"rackId\", \"systemRackPos\" "
."from \"vBuildTemplate\" ";
//."where \"systemHostname\" not like '%.internal' ";

if (isset($ROOM)) {
  $sql .= "and \"roomName\" = '$ROOM' ";
  $page = $ROOM;
}

$sql .= "order by \"roomName\", \"rackId\", \"systemRackPos\" desc;";

$allnodes = pg_query($sql);

if ($allnodes and pg_num_rows($allnodes)) {
    if (!$do_layout) {
        echo "<div class=\"room\" id=\"room_$ROOM\">\n";
        echo "<p class=\"room\">$ROOM</p><br />\n";
    }

    while ($r = pg_fetch_row($allnodes)) {
        /* Start of main loop... */
        // We're looking at this node
        $node = $r[0];
        $short = explode(".", $node);
        $short = $short[0];
        $pos = (42 - $r[3]) * 12;

        if ($do_layout) {
            $newroom = false;
            // In this room
            if ($r[1] != $room) {
                if ($room != '') {
                    echo "</div>\n";
                    echo "</div>\n";
                }
                $room = $r[1];
                $newroom = true;
                echo "<div class=\"room\" id=\"room_$room\">\n";
                echo "<h2 class=\"room\">$room</h2>\n";
            }

            if ($r[2] != $rack) {
                if ($rack != '' and !$newroom) {
                    echo "</div>\n";
                }
                $rack = $r[2];
                echo "<div class=\"rack\" id=\"rack_$rack\">\n";
                echo "<h5 class=\"rack\">$rack</h5>\n";
            }
        }

        // Set defaults
        $nodeStatus = "";
        $nodeNote = "";
        $nodeInfo = "$node";

        $mynode = mysql_query("select state, note from state LEFT JOIN notes on (notes.name=state.name) where state.name='$node';");
        if ($mynode and mysql_num_rows($mynode)) {
          $mn_r = mysql_fetch_row($mynode);
          $nodeStatus = $mn_r[0];
          $nodeNote = $mn_r[1];
          $nodeInfo .= " (Batch: $nodeStatus)";
        }

        $nagios_hosts=array();
        // Process nagios state info
        if (array_key_exists($short, $nagios_hosts)) {
            $nagios_nodedata = $nagios_hosts[$short];

            if ($nagios_nodedata[$nagios_columns["scheduled_downtime_depth"]] > 0) {
                $nodeStatus = "downtime";
                $nodeInfo = "$node ($nodeStatus - Nagios)";
            }
            elseif ($nagios_nodedata[$nagios_columns["state"]] == 1) {
                $nodeStatus = "down";
                $nodeInfo = "$node ($nodeStatus - Nagios)";
            }
            else {
                // Check for Nagios alarms if system is not down, or in downtime
                if ($nagios_nodedata[$nagios_columns["num_services_crit"]] > 0) {
                    $nodeStatus .= " critical";
                }
                elseif ($nagios_nodedata[$nagios_columns["num_services_warn"]] > 0) {
                    $nodeStatus .= " warning";
                }
            }
        }

        // Process notes
        if (strlen($nodeNote) > 0) {
            // Tack note onto end of info string
            $nodeInfo .= ' - '.$nodeNote;

            // We want to be case insensitive!
            $s = strtolower($nodeNote);
            $nodeStatus .= ' note';
        }

        if (!$do_layout) {
            $nodeStatus .= ' float';
        }

        // Apply castor status
        //$nodeStatus .= " castor" . $currStat;

        // And show it
        echo '<span id="n_'.$short.'" onclick="node(\''.$node.'\')" class="node '.$nodeStatus.'" title="'.htmlentities($nodeInfo).'"></span>';
    }
    if ($do_layout) {
        echo "</div>\n";
        echo "</div>\n";
    }
    echo "</div>\n";
}
