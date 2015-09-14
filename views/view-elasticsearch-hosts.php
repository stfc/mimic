<?php
require("header.php"); // Important includes

// Config
$ES_URL = $CONFIG['URL']['ES'] . $CONFIG['PORT']['ES_PORT'];

$SHARD_STATES = Array(
    'STARTED' => 'free',
    'RELOCATING' => 'full',
    'INITIALIZING' => 'offline',
    'UNASSIGNED' => 'batchdown',
);

$nodes = file_get_contents("$ES_URL/_cluster/state/nodes");
$nodes = json_decode($nodes, true);
$nodes = $nodes['nodes'];

// Add a fake node called "unassigned" so that unassigned shards are grouped on the display
$nodes['unassigned'] = Array('name' => 'unassigned');

$health = file_get_contents("$ES_URL/_cluster/health/?level=cluster");
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

echo "<h2 class='group-name' style='text-shadow: 1px 1px 4px {$health['status']};'>{$cluster['cluster_name']}</h2>\n";
echo "<div class='node-group'>\n";
foreach ($nodes as $node_id => $node) {
    if (!$node['attributes']['client']) {
        $node_name = $node['name'];
        echo "<div class=\"node-panel grid-item\">\n";
        echo "<h5 class=\"node-name\" style=\"text-shadow: 1px 1px 4px black;\" title=\"$node_name\">$node_name</h5>\n";
        foreach ($host_shards[$node_id] as $shard) {
            $shard_class = $SHARD_STATES[$shard['state']];
            if (! $shard['primary']) {
                $shard_class .= ' replica';
            }

            $shard_info = "";
            unset($shard['node']);
            if ($shard['state'] != 'RELOCATING') {
                unset($shard['relocating_node']);
            }
            foreach ($shard as $key => $value) {
                // If this property looks like a node ID, look it up and replace it with the hostname of the node
                if (strpos($key, 'node') !== false) {
                    $value = $nodes[$value]['name'];
                }
                $value = bool2str($value);
                $shard_info .= sprintf("<p><b>%s</b><br>%s</p>", $key, $value);
            }
            echo "<span id='n_{$shard['index']}_{$shard['shard']}' class='node $shard_class' title='$shard_info'></span>";
        }
        echo "</div>\n";
    }
}
echo "</div>\n";
