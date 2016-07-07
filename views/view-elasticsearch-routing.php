<?php

/*

  Build a index-centric view of ElasticSearch clusters

  Throughout this code words such as cluster and node are used in both an ElasticSearch and a Mimic content.
  In order to differentiate them variables have been prefixed with `es_` and `mimic_` respectively.

*/

require("header.php"); // Important includes

// Configuration
$config = Array(
    "clickable" => false,
);

// List of clusters to gather data from
// really should be loaded from config or something more dynamic
$es_clusters = Array(
    'sawmill' => $CONFIG['URL']['ES'] . $CONFIG['PORT']['ES_PORT'],
);

$view = Array(); // The whole view that we return to Mimic
$view['config'] = $config;
foreach ($es_clusters as $es_cluster_name => $es_cluster_url) {
    // Build a lookup table for node details
    // the key being the internal node id (e.g. Yi2lqwdDQ9C1OC0ouYbfyQ)
    $es_nodes = file_get_contents("$es_cluster_url/_cluster/state/nodes");
    if ($es_nodes === false) {
        error("No data returned from", "elasticsearch");
    }
    $es_nodes = json_decode($es_nodes, true);
    $es_nodes = $es_nodes['nodes'];

    // Lookup table for detailed cluster health
    // Currently unused, was used for cluster and index health,
    // but new views no longer support status at those levels
    //$es_health = file_get_contents("$es_cluster_url/_cluster/health/?level=shards");
    //$es_health = json_decode($es_health, true);

    // Lookup table for cluster index shard routing, used to build list of indices
    $es_routing = file_get_contents("$es_cluster_url/_cluster/state/routing_table");
    $es_routing = json_decode($es_routing, true);
    $es_indices = $es_routing['routing_table']['indices'];
    $es_index_names = array_keys($es_routing['routing_table']['indices']);
    sort($es_index_names);

    $mimic_group = Array(); // Set of panels of clusters of nodes in this ES cluster (i.e. the whole cluster)

    foreach ($es_index_names as $index_name) {
        $index_data = $es_indices[$index_name];
        $shard_ids = array_keys($index_data['shards']);
        sort($shard_ids);

        $mimic_panel = Array(); // Set of shard allocations belonging to a single index

        foreach ($shard_ids as $shard_id) {
            $replica_ids = array_keys($index_data['shards'][$shard_id]);
            sort($replica_ids);

            $mimic_cluster = Array(); // Set of shards (primary and replicas) belonging to a single allocation

            foreach ($replica_ids as $replica_id) {
                $shard_data = $index_data['shards'][$shard_id][$replica_id];

                $mimic_node = Array(); // Set of shard properties (status etc)

                foreach ($shard_data as $key => $value) {
                    $value = bool2str($value); // Convert any booleans to strings (because PHP)
                    if (strlen($value) > 0) { // Only include properties which have a value
                        if (strpos($key, 'node') !== false) { // If the property looks like a node ID, replace it with the hostname of the node
                            $value = $es_nodes[$value]['name'];
                        }
                        $mimic_node[$key] = $value;
                    }
                }

                // Build the status dictionary that Mimic expects
                $status = $shard_data['state'];
                if (! $shard_data['primary']) {
                    $status .= ' replica';
                }
                $mimic_node['status'] = Array();
                $mimic_node['status'][$status] = $es_cluster_name; // We don't have a better "source" than the name of the cluster

                $mimic_cluster["Shard {$shard_id} (Replica $replica_id)"] = $mimic_node; // Add shard details to set of shards
            }
            $mimic_panel[$shard_id] = $mimic_cluster;
        }
        $mimic_group[$index_name] = $mimic_panel;
    }
    $view[$es_cluster_name] = $mimic_group;
}
echo json_encode($view);
