<?php
require("header.php"); // Important includes

// Configuration
$AQUILON_URL = $CONFIG['URL']['AQUILON'];
$config = Array(
    "clickable" => true,
);

// branch and personality for nodes
$url = "$AQUILON_URL/cgi-bin/report/host_personality_branch_ceph";
$jsondata = file_get_contents($url);
if ($jsondata === false) {
    error("No data returned from", "aquilon");
}
$personalities = json_decode($jsondata, true);
uksort($personalities, "strnatcmp");


$OSD_DATA_SOURCES = Array(
    'echo' => 'http://ceph-adm1.gridpp.rl.ac.uk/ceph_osd_tree.json',
);

$osd_hosts = Array();

foreach ($OSD_DATA_SOURCES as $cluster => $url) {
    $osd_data = file_get_contents($url);
    $osd_data = json_decode($osd_data, true);

    foreach ($osd_data['nodes'] as $i => $node) {
        if ($node['type'] == 'host') {
            $osd_hosts[$node['name']] = $cluster;
        }
    }
}


$service_data = file_get_contents("$AQUILON_URL/cgi-bin/report/report_service_clients");
$service_data = json_decode($service_data, true);

// Generates main array
$results = Array();
$results['config'] = $config;

// Gets notes for nodes
$all_notes = Array();
$notes = $SQL->query("select name, note from notes");
if ($notes and $notes->num_rows) {
    while ($note = $notes->fetch_assoc()) {
        $all_notes[$note['name']] = $note['note'];
    }
}


foreach ($personalities as $name => $personality) {
    $group = '';
    $panel = $personality["personality"];
    $cluster = '';
    $short = explode('.', $name, 2);
    $short = $short[0];

    if (array_key_exists($name, $service_data)) {
        if (array_key_exists('ceph', $service_data[$name])) {
            $group = $service_data[$name]['ceph'].' (aq)';
        };
    };

    if (array_key_exists($short, $osd_hosts)) {
        $group = $osd_hosts[$short].' (osd tree)';
    };

    $results[$group][$panel][$cluster][$name] = $personality;

    $results[$group][$panel][$cluster][$name]['short'] = $short;

    if (array_key_exists($name, $all_notes)) {
        $results[$group][$panel][$cluster][$name]['note'] = $all_notes[$name];
    };

    if (nagios($name) !== Null) {
        $results[$group][$panel][$cluster][$name]['nagios'] = nagios($name);
    };
}

// Returns built json
echo json_encode($results);
