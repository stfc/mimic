<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch?filter=nubes");
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$all_nodes = json_decode($jsondata, true);
uksort($all_nodes, "strnatcmp");

// Gets list of instantiated vms
$vmoutput = shell_exec('/usr/bin/python ../xmlrpc/vmpoolinfo.py');
$vmxml = simplexml_load_string($vmoutput, null, LIBXML_NOCDATA);
$instantiated = Array();
foreach ($vmxml as $host) {
    $instantiated[(string) $host->TEMPLATE->NIC->IP] = "uninstantiated";
}

// Gets list of hypervisor cpu states
$hvoutput = shell_exec('/usr/bin/python ../xmlrpc/hostpoolinfo.py');
$hvxml = simplexml_load_string($hvoutput, null, LIBXML_NOCDATA);

$hvstatus = Array();
foreach ($hvxml as $host) {

    $name = (string) $host->NAME;
    $cpu_usage = $host->HOST_SHARE->CPU_USAGE;
    $max_cpu_usage = $host->HOST_SHARE->MAX_CPU;
    $state = "inuse";
    if ($cpu_usage >= $max_cpu_usage * 0.9) {
        $state = "full";
    }
    if ($cpu_usage <= 0) {
        $state = "free";

    }
    $hvstatus[$name][$state] = 'OpenNebula';
}

// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
$results = Array();
foreach ($all_nodes as $name => $panels) {
    if (strpos($panels["branch_name"], "openstack") !== false || strpos($panels["personality"], "openstack") !== false) {
        $group = "infrastructure";
        $panel = $panels["personality"];
        $cluster = '';

        $results[$group][$panel][$cluster][$name] = Array();
        if (array_key_exists($name, $all_nodes)) {
            unset($all_nodes[$name]['personality']);
            ksort($all_nodes[$name]);
            $results[$group][$panel][$cluster][$name] = $all_nodes[$name];
        }
        if (array_key_exists($name, $all_notes)) {
            $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
        }
        if (nagios($name) !== Null) {
            $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
        };

        if ($panel == "opennebula-hypervisor" && array_key_exists($name, $hvstatus)) {
            $results[$group][$panel][$cluster][$name]['status'] = $hvstatus[$name];
        }

        if (strpos($name, "vm") !== false) {
            $all_status = Array();

            $state = 'instantiated';
            if (!array_key_exists($all_nodes[$name]['ip'], $instantiated)) {
                $state = 'uninstantiated';
            }
            $all_status[$state] = 'OpenNebula';
            $results[$group][$panel][$cluster][$name]['status'] = $all_status;
        }
    }
}

// Returns built json
echo json_encode($results);
