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
    ),

    'Cloud' => array(
        'Overview' => array(
            'name' => 'Overview',
            'text' => 'Overview of cloud nodes',
            'link' => 'cloud'
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
