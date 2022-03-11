<?php
// 'Section name' => array(
//     'Name (not for display)' => array(
//         'name' => 'Display name',
//         'text' => 'HTML title',
//         'link' => 'File name',
//     ),
// ),
$menu = array(
    'Generational' => array(
        'Workers' => array(
            'name' => 'Workers',
            'text' => 'Logical overview of worker nodes',
            'link' => 'logical-workers',
        ),
        'Storage' => array(
            'name' => 'Storage',
            'text' => 'Generational overview of storage nodes',
            'link' => 'generational-storage'
        ),
        'Overview' => array(
            'name' => 'Overview',
            'text' => 'Generational overview of all nodes',
            'link' => 'generational-all'
        ),
    ),

    'Logical' => array(
        'Storage' => array(
            'name' => 'Storage',
            'text' => 'Logical overview of storage nodes',
            'link' => 'logical-storage',
        ),
        'CEPH' => array(
            'name' => 'CEPH',
            'text' => 'Logical overview of CEPH clusters and related nodes',
            'link' => 'logical-ceph',
        ),
        'SCARF' => array(
            'name' => 'SCARF',
            'text' => 'Overview of SCARF nodes',
            'link' => 'logical-scarf',
        ),
    ),

    'Aquilon' => array(
        'Sandboxes & Prod' => array(
            'name' => 'Sandboxes & Prod',
            'text' => 'Nodes in aquilon domains and sandboxes',
            'link' => 'aquilon-sandboxes'
        ),
        'Personalities' => array(
            'name' => 'Personalities',
            'text' => 'Nodes with aquilon personalities',
            'link' => 'aquilon-personalities'
        ),
        'Services' => array(
            'name' => 'Services',
            'text' => 'Servers and clients of aquilon services',
            'link' => 'aquilon-services'
        ),
        'Clusters' => array(
            'name' => 'Clusters',
            'text' => 'Clustered hosts',
            'link' => 'aquilon-clusters'
        ),
        'GRNs' => array(
            'name' => 'GRNs',
            'text' => 'Hosts grouped by owning group and personality',
            'link' => 'aquilon-grn'
        ),
    ),

    'Cloud' => array(
        'OpenStack' => array(
            'name' => 'OpenStack',
            'text' => 'OpenStack Cloud nodes',
            'link' => 'cloud-openstack'
        ),
    ),

    'Elasticsearch' => array(
        'Routing' => array(
            'name' => 'Routing',
            'text' => 'Elasticsearch Routing Table',
            'link' => 'elasticsearch-routing'
        ),
        'Hosts' => array(
            'name' => 'Hosts',
            'text' => 'Elasticsearch Shards by Host',
            'link' => 'elasticsearch-hosts'
        ),
    ),

    'Key' => array(
        'Key' => array(
            'other' => "<div class='key'></div>",
        ),
    ),
);
