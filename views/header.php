<?php
require("inc/config-call.inc.php");
require("inc/functions.inc.php");
require("inc/ds-mklivestatus.inc.php");
require("inc/db-open.inc.php"); // MySQL Data sources
header('Content-Type: application/json');

function bool2str($value) {
    // PHP is pretty bad at representing booleans in a human readable way so we'll do it ourselves
    if ($value === true) {
        $value = "true";
    } elseif ($value === false) {
        $value = "false";
    }
    return($value);
}

# MK Livestatus

$mklivestatus_data = mkLiveStatus::get_hosts();

function mklivestatus_state($short, $node, $nodeStatus) {
    global $mklivestatus_data;
    $mklivestatusInfo = '';
    // Process mklivestatus state info
    if (is_array($mklivestatus_data)) {
        foreach ($mklivestatus_data as $server => $mklivestatus_hosts) {
            $mklivestatus_nodedata = null;
            if (array_key_exists($short, $mklivestatus_hosts)) {
                $mklivestatus_nodedata = $mklivestatus_hosts[$short];
            }
            elseif (array_key_exists($node, $mklivestatus_hosts)) {
                $mklivestatus_nodedata = $mklivestatus_hosts[$node];
            }
            if (sizeof($mklivestatus_nodedata) > 0) {
                if ($mklivestatus_nodedata["scheduled_downtime_depth"] > 0) {
                    $nodeStatus = "downtime";
                    $mklivestatusInfo .= "($nodeStatus - $server)";
                }
                elseif ($mklivestatus_nodedata["state"] == 1) {
                    $nodeStatus = "down";
                    $mklivestatusInfo .= " ($nodeStatus - $server)";
                }
                else {
                    // Check for alarms if system is not down, or in downtime
                    if ($mklivestatus_nodedata["num_services_crit"] > 0) {
                        $nodeStatus .= "critical";
                        $mklivestatusInfo .= "(critical - $server)";
                    }
                    elseif ($mklivestatus_nodedata["num_services_warn"] > 0) {
                        $nodeStatus .= "warning";
                        $mklivestatusInfo .= "(warning - $server)";
                    }
                }
            }
        }
        return(Array($nodeStatus,$mklivestatusInfo));
    }
    return(Null);
}

function mklivestatus_oneline($node_name, $shorten=true) {
    $short = $node_name;
    if ($shorten) {
        $short = explode(".", $node_name);
        $short = $short[0];
    }
    $nodeStatus = "";
    $ntup = mklivestatus_state($short, $node_name, $nodeStatus);
    if (count($ntup) > 1 && !empty($ntup[1])) {
        $nodeStatus = $ntup[0];
    }
    return ' '.$nodeStatus; // Must have whitespace prepended
}

function get_aquilon_report($config, $report) {
    $url = $config['URL']['AQUILON']."/cgi-bin/report/$report";
    $user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_STRING);

    if ($user) {
        $url .= "?owner=$user";
    };
    $jsondata = file_get_contents($url);
    if ($jsondata === false) {
        error("No data returned from", "aquilon");
    }
    $result = json_decode($jsondata, true);
    uksort($result, "strnatcmp");

    return $result;
}
