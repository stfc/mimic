<?php
//Important includes
require("header.php");

$SHARD_STATES = Array(
    'STARTED' => 'free',
    'RELOCATING' => 'full',
    'INITIALIZING' => 'offline',
    'UNASSIGNED' => 'batchdown',
    );

function bool2str($v) {
  // PHP is pretty bad at representing booleans in a human readable way so we'll do it ourselves
  if ($v === true) {
      $v = "true";
  } elseif ($v === false) {
      $v = "false";
  }
  return($v);
}

$nodes = file_get_contents($CONFIG['ES']['URL'] . "_cluster/state/nodes");
$nodes = json_decode($nodes, true);
$nodes = $nodes['nodes'];

$health = file_get_contents($CONFIG['ES']['URL'] . "_cluster/health/?level=shards");
$health = json_decode($health, true);
$health = $health;

$cluster = file_get_contents($CONFIG['ES']['URL'] . "_cluster/state/routing_table");
$cluster = json_decode($cluster, true);

$indices = $cluster['routing_table']['indices'];
$index_names = array_keys($cluster['routing_table']['indices']);
sort($index_names);

echo "<div style='float: none; clear: both; position: relative; top: 60px;'>\n";
echo "<p class='cluster' style='font-size: 18pt; padding: 4px; text-shadow: 1px 1px 4px {$health['status']};'>{$cluster['cluster_name']}</p>\n";
foreach ($index_names as $index_name) {
    $index_data = $indices[$index_name];
    echo "<div class=\"cluster\">\n";
    echo "<p class=\"cluster\" style=\"text-shadow: 1px 1px 4px {$health['indices'][$index_name]['status']};\" title=\"Index: $index_name\">$index_name</p>\n";
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
            foreach ($shard_data as $k => $v) {
                // If this property looks like a node ID, look it up and replace it with the hostname of the node
                if (strpos($k, 'node') !== false) {
                    $v = $nodes[$v]['name'];
                }
                $v = bool2str($v);
                // Only show properties with a value
                if (strlen($v) > 0) {
                    printf("<p><b>%s</b><br>%s</p>", $k, $v);
                }
            }
            echo "\"></span>\n";
        }
        echo "</div>\n";
    }
    echo "</div>\n";
}
echo "</div>\n";

?>
