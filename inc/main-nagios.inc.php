<?php

# Nagios Livestatus
require_once("ds-nagioslivestatus.inc.php");

$nagios_data = nagiosLiveStatus::get_hosts();

function nagios_state($short, $node, $nodeStatus) {
    global $nagios_data;
    $nagiosInfo = '';
    // Process nagios state info
    if (is_array($nagios_data)) {
        foreach ($nagios_data as $server => $nagios_hosts) {
            $nagios_nodedata = null;
            if (array_key_exists($short, $nagios_hosts)) {
                $nagios_nodedata = $nagios_hosts[$short];
            }
            elseif (array_key_exists($node, $nagios_hosts)) {
                $nagios_nodedata = $nagios_hosts[$node];
            }
            if (sizeof($nagios_nodedata) > 0) {
                if ($nagios_nodedata["scheduled_downtime_depth"] > 0) {
                    $nodeStatus = "downtime";
                    $nagiosInfo .= "($nodeStatus - $server)";
                }
                elseif ($nagios_nodedata["state"] == 1) {
                    $nodeStatus = "down";
                    $nagiosInfo .= " ($nodeStatus - $server)";
                }
                else {
                    // Check for Nagios alarms if system is not down, or in downtime
                    if ($nagios_nodedata["num_services_crit"] > 0) {
                        $nodeStatus .= "critical";
                        $nagiosInfo .= "(critical - $server)";
                    }
                    elseif ($nagios_nodedata["num_services_warn"] > 0) {
                        $nodeStatus .= "warning";
                        $nagiosInfo .= "(warning - $server)";
                    }
                }
            }
        }
        return(Array($nodeStatus,$nagiosInfo));
    }
    return(Null);
}
