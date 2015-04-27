<?php

# Nagios Livestatus
require("ds-nagioslivestatus.inc.php");

$nagios_data = nagiosLiveStatus::get_hosts();
$nagios_columns = $nagios_data[0];
$nagios_hosts = $nagios_data[1];
unset($nagios_data);


function nagios_state($short, $node, $nodeInfo, $nodeStatus) {
    global $nagios_hosts;
    global $nagios_columns;
    // Process nagios state info
    if (is_array($nagios_hosts) && array_key_exists($short, $nagios_hosts)) {
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
        return(Array($nodeStatus,$nodeInfo));
    }
	return(Null);
}

?>
