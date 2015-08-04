<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];

// Gets node data and formats it
$jsondata = file_get_contents("$AQUILON_URL/cgi-bin/report/host_personality_branch?filter=nubes");
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
    $hvstatus[$name]['status']['state'] = "inuse";
    if ($cpu_usage >= $max_cpu_usage * 0.9) {
        $hvstatus[$name]['status']['state'] = "full";
    }
    if ($cpu_usage <= 0) {
        $hvstatus[$name]['status']['state'] = "free";
    }
}

// Gets notes for nodes
$all_notes = Array();
$notes = mysql_query("select name, note from notes");
if ($notes and mysql_num_rows($notes)) {
    while ($note = mysql_fetch_assoc($notes)) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
$results = Array();
foreach ($all_nodes as $name => $panels) {

    $group = "infrastructure";
    $panel = $panels["personality"];
    $cluster = '';

    if (strpos($name, "vm") !== false) {
       $group = "vm";
    }

    $results[$group][$panel][$cluster][$name] = Array();
    if (array_key_exists($name, $all_nodes)) {
        $results[$group][$panel][$cluster][$name] = $all_nodes[$name];
    }
    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    }
    if ($group == "vm") {
        $results[$group][$panel][$cluster][$name]['status']['state'] = "instantiated";
        if (!array_key_exists($all_nodes[$name]['ip'], $instantiated)) {
            $results[$group][$panel][$cluster][$name]['status']['state'] = "uninstantiated";
        }
    }
    if ($panel == "cloud-prod-hypervisor") {
        $results[$group][$panel][$cluster][$name]['status']['state'] = $hvstatus[$name]['status']['state'];
    }
}

// Renders page
display($results);
