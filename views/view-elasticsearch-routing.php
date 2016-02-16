<?php
require("header.php"); // Important includes

// Config
$ES_URL = $CONFIG['URL']['ES'] . $CONFIG['PORT']['ES_PORT'];

$nodes = file_get_contents("$ES_URL/_cluster/state/nodes");
if ($nodes === false) {
    error("No data returned from", "elasticsearch");
}
$nodes = json_decode($nodes, true);
$nodes = $nodes['nodes'];

$health = file_get_contents("$ES_URL/_cluster/health/?level=shards");
$health = json_decode($health, true);
$health = $health;

$cluster = file_get_contents("$ES_URL/_cluster/state/routing_table");
$cluster = json_decode($cluster, true);

$indices = $cluster['routing_table']['indices'];
$index_names = array_keys($cluster['routing_table']['indices']);
sort($index_names);



$host_shards = Array();
foreach ($indices as $index_name => $index) {
    foreach ($index['shards'] as $shards) {
        foreach ($shards as $shard ) {
            $node = 'unassigned';
            if ($shard['node']) {
                $node = $shard['node'];
            }
            $shard['index'] = $index_name;
            if (!array_key_exists($node, $host_shards)) {
                $host_shards[$node] = Array();
            }
            array_push($host_shards[$node], $shard);
        }
    }
}


$results = Array();


foreach ($indices as $panel_name => $panel) {

    foreach ($panel as $cluster_name => $clusterthing) {

        foreach ($clusterthing as $shard_name => $shard) {

            // $index_name = $shard['index'];
            $shard_info = Array();

            $status = Array();
            // $status[$shard['state']] = $cluster['cluster_name'];

            unset($shard['state']);

            foreach ($shard as $key => $value) {
                // If this property looks like a node ID, look it up and replace it with the hostname of the node
                if (strpos($key, 'node') !== false) {
                    $value = $nodes[$value]['name'];
                }
                $value = bool2str($value);
                $shard_info[$key] = $value;
            }

            // if ($status['state'] != 'RELOCATING') {
            //     unset($shard['relocating_node']);
            // }


            // if ($shard['primary']) {
            //     $shard_info['type'] = 'primary';
            // } else {
            //     $shard_info['type'] = 'replica';
            //     $status[$shard['state'].' replica'] = $cluster['cluster_name'];
            // }

            // $shard_id = $shard_info['shard'];
            // unset($shard_info['shard']);

            $results[$panel_name][$shard_name] = $shard_info;
            // $results[$panel_name][$shard_name]['status'] = $status;


        }
    }
}


$groups = Array(
    $cluster['cluster_name'] => $results,
);

echo json_encode($groups);

// $host_shards
// echo json_encode($results);
exit;

foreach ($index_names as $index_name) {
    $index_data = $indices[$index_name];
    echo "<div class=\"node-panel grid-item\">\n";
    echo "<h5 class=\"cluster-name\" style=\"text-shadow: 1px 1px 4px {$health['indices'][$index_name]['status']};\" title=\"Index: $index_name\">$index_name</h5>\n";
    $shard_ids = array_keys($index_data['shards']);
    sort($shard_ids);
    foreach ($shard_ids as $shard_id) {
        echo "<div class=\"rack\">\n";
        echo "<p class=\"rack\">$shard_id</p>\n";
        $replica_ids = array_keys($index_data['shards'][$shard_id]);
        sort($replica_ids);
        foreach ($replica_ids as $replica_id) {
            $shard_data = $index_data['shards'][$shard_id][$replica_id];
            $status = $SHARD_STATES[$shard_data['state']];
            if (! $shard_data['primary']) {
                $status .= ' replica';
            }
            echo "<span class=\"node $status\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"<h4>Shard {$shard_id} (Replica $replica_id)</h4>";
            foreach ($shard_data as $key => $value) {
                // If this property looks like a node ID, look it up and replace it with the hostname of the node
                if (strpos($key, 'node') !== false) {
                    $value = $nodes[$value]['name'];
                }
                $value = bool2str($value);
                // Only show properties with a value
                if (strlen($value) > 0) {
                    printf("<p><b>%s</b><br>%s</p>", $key, $value);
                }
            }
            echo "\"></span>\n";
        }
        echo "</div>\n";
    }
    echo "</div>\n";
}
