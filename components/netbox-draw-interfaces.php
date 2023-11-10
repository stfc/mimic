<?php

require_once("inc/config-call.inc.php");
require_once("inc/ds-netbox.inc.php");
require_once("inc/db-magdb-open.inc.php");
require_once("inc/ouilookup.inc.php");
require_once("inc/functions.inc.php");

$netbox = new netbox();

$STYLE_GRAPH = '
    graph [bgcolor=transparent]
    dpi=72
    overlap=none
    rankdir="LR"
    concentrate=true
    node [shape="box" fontsize=8 fontname="sans-serif" height=0 style=filled fillcolor=white width=1.6]
    edge [dir=none]
';

$STYLE_DEVICE = '
    style="filled"
    bgcolor="#babdb6"
    fontsize=9
    fontname="sans-serif"
';

$STYLE_BOND = '
    style="filled"
    color="#75507b"
    fillcolor="#ad7fa8"
';

$STYLE_LLDP = '
    tooltip="LLDP Information"
    color="#8f5902"
    fillcolor="#e9b96e"
';

$STYLE_BOOTABLE = '
    color="#204a87"
    fillcolor="#729fcf"
';

$STYLE_MANAGEMENT = '
    color="#4e9a06"
    fillcolor="#73d216"
';

$STYLE_BOOTABLE_MANAGEMENT = '
    color="#377247"
    fillcolor="#73d216,0.5:#729fcf"
    gradientangle=162
';

$STYLE_DNS = '
    color="#555753"
    fillcolor="#d3d7cf"
';

$STYLE_UNKNOWN = '
    style="dashed"
    color="#888a85"
    fontcolor="#888a85"
';

$netbox_id = filter_input(INPUT_GET, 'netbox_id', FILTER_SANITIZE_NUMBER_INT);
$is_vm = filter_input(INPUT_GET, 'is_vm', FILTER_SANITIZE_NUMBER_INT);

$graph_text = "";

$DNS_CACHE_FILE = "cache/dns-aliases.json";

$dns = array();

if (file_exists($DNS_CACHE_FILE)) {
    $dns = file_get_contents($DNS_CACHE_FILE);
    $dns = str_replace("'", '"', $dns);
    $dns = json_decode($dns, True);
}

$graph_text .= "digraph \"aliases\" {\n$STYLE_GRAPH";

if ($netbox_id) {
    if ($is_vm) {
        $interfaces = $netbox->netbox_search('/virtualization/interfaces/', array('virtual_machine_id' => $netbox_id, 'limit' => 128));
    } else {
        $interfaces = $netbox->netbox_search('/dcim/interfaces/', array('device_id' => $netbox_id, 'limit' => 128));
    }

    // Last Seen
    $observed_ips = pg_fetch_all(pg_query('select "ipAddress", EXTRACT(EPOCH FROM now() - "lastSeen") as "lastSeen", date_trunc(\'day\', "lastSeen") = date_trunc(\'day\', now()) as "today" from "ipSurvey"'));
    $lastseen = Array();
    foreach ($observed_ips as $l) {
        if(!isset($lastseen[$l["ipAddress"]])){
            $lastseen[$l["ipAddress"]] = $l["lastSeen"];
        }
    }
    unset($observed_ips);

    if ($interfaces or $records) {
        arsort($interfaces);
        $count_ip = 0;
        foreach ($interfaces as $i) {
            $i["mac_address"] = strtolower($i["mac_address"]); //backward compatability with MagDB for now
            $style = "";
            if ($is_vm) {
                $i["type"] = Array(
                    "label" => 'VM Interface',
                    "value" => 'vm-interface',
                );
            }
            $tooltip = $i["type"]["value"];
            if (array_key_exists('mgmt_only', $i) and $i['mgmt_only']) {
                $style = $STYLE_MANAGEMENT;
                $tooltip = "Management";
            }
            foreach ($i['tags'] as $_ => $tag) {
                if ($tag['slug'] == 'bootable') {
                   if ($style) {
                       $style = $STYLE_BOOTABLE_MANAGEMENT;
                       $tooltip = "Bootable Management";
                   } else {
                       $style = $STYLE_BOOTABLE;
                       $tooltip = "Bootable";
                   }
                }
            }
            $vendor = '&hellip;';
            if ($i["mac_address"]) {
                $vendor = ouilookup($i["mac_address"]);
            } else {
                $i["mac_address"] = 'No MAC Recorded';
            }
            $graph_text .= sprintf(
                '"interface.%s" [label="%s\n%s\n%s\n%s" URL="%s" target="_parent" tooltip="%s" %s];'."\n",
                $i["id"],
                $i["name"],
                $i["mac_address"],
                $vendor,
                $i["type"]["label"],
                str_replace('/api/', '/', $i["url"]),
                $tooltip,
                $style
            );

            if ($i['link_peers']) {
                foreach ($i['link_peers'] as $peer) {
                    $graph_text .= 'subgraph "cluster_peer.'.$peer['device']['id'].'"{'."\n";
                    $graph_text .= $STYLE_DEVICE."\n";
                    $graph_text .= sprintf(
                        '  label="%s" URL="%s" target="_parent"'."\n",
                        $peer['device']['name'],
                        '/node.php?n='.$peer['device']['name']
                    );

                    $graph_text .= sprintf(
                        '  "interface.%s" [label="%s" URL="%s" target="_parent"]'."\n",
                        $peer['id'],
                        $peer['name'],
                        str_replace('/api/', '/', $peer['url'])
                    );

                    $graph_text .= '}'."\n";
                }
            }
            else {
                $unknown_interface = "unknown.interface.{$i["id"]}";
                $graph_text .= "\"$unknown_interface\" [label=\"Unknown Interface\" $STYLE_UNKNOWN];\n";
                $graph_text .= "\"$unknown_interface\" -> \"interface.{$i["id"]}\" [$STYLE_UNKNOWN];\n";
            }
        }

        $cables = null;
        if (!$is_vm) {
            $cables = $netbox->netbox_search('/dcim/cables/', array(
                'type__ne' => 'power',
                'device_id' => $i['device']['id'],
                'limit' => 128,
            ));
        };
        if ($cables) {
            foreach ($cables as $cable) {
                foreach ($cable['a_terminations'] as $a_term) {
                    foreach ($cable['b_terminations'] as $b_term) {
                        $color = '#000000';
                        $width = '1';
                        if ($cable['color']) {
                            $color = '#'.$cable['color'];
                            $width = '4';
                        };
                        $left_side = $b_term['object_id'];
                        $right_side = $a_term['object_id'];

                        if ($b_term['object']['device']['id'] == $netbox_id) {
                            $left_side = $a_term['object_id'];
                            $right_side = $b_term['object_id'];
                        };

                        $graph_text .= sprintf(
                            '  "interface.%s" -> "interface.%s" [tooltip="%s" penwidth=%s URL="%s" target="_parent" color="%s"]'."\n",
                            $left_side,
                            $right_side,
                            $cable['display'],
                            $width,
                            str_replace('/api/', '/', $cable['url']),
                            $color
                        );
                    }
                }
            }
        }

        if ($is_vm) {
            $ip_addresses = $netbox->netbox_search('/ipam/ip-addresses/', array(
                "assigned_object_type" => "virtualization.vminterface",
                'virtual_machine' => $i['virtual_machine']['name'],
            ));
        } else {
            $ip_addresses = $netbox->netbox_search('/ipam/ip-addresses/', array(
                "assigned_object_type" => "dcim.interface",
                'device' => $i['device']['name'],
            ));
        };
        foreach ($ip_addresses as $r) {
            $graph_text .= '"interface.'.$r["assigned_object"]["id"].'" -> "'.$r["address"].'";'."\n";
            $seen = "Never Seen";
            $count_ip += 1;
            $ip = explode('/', $r["address"], 2)[0];

            foreach ($r['tags'] as $_ => $tag) {
                if (strpos($tag['name'], 'lastseen:') !== false) {
                   $seen = 'Last Seen '.ucwords(explode(':', $tag['name'], 2)[1]);
                }
            }

            if ($seen == 'Never Seen' && isset($lastseen[$ip])) {
                $seen = $lastseen[$ip];
                if ($seen <= 86400) {
                    $seen = "Last Seen Today";
                } else {
                    $seen = 'Last Seen '.prettytime($seen);
                }
            }
            $fillcolor = 'white';
            $color = 'black';
            $style='rounded,filled';
            if ($r['status']['value'] == 'deprecated') {
                $fillcolor = '#d3d7cf7f';
                $color = '#888a85';
                $style="$style,dashed";
            } else if ($r['status']['value'] == 'reserved') {
                $fillcolor = '#729fcf7f';
                $color = '#204a87';
                $style="$style,dashed";
            };
            $graph_text .= '"'.$r["address"].'" [style="'.$style.'" fillcolor="'.$fillcolor.'" color="'.$color.'" fontcolor="'.$color.'" label="'.$r["address"].'\n '.$seen.'" URL="'.str_replace('/api/', '/', $r["url"]).'" target="_parent" tooltip="'.$r['status']['label'].'"];'."\n";
            if ($r["dns_name"]) {
                $graph_text .= '"'.$r["dns_name"].'" [style="'.$style.'" fillcolor="'.$fillcolor.'" color="'.$color.'" fontcolor="'.$color.'" URL="/node.php?n='.$r["dns_name"].'" target="_parent" tooltip="'.$r['status']['label'].'"];'."\n";
                $graph_text .= '"'.$r["address"].'" -> "'.$r["dns_name"].'" [color="'.$color.'"];'."\n";
            }
        }
    } else {
        $graph_text .= "\"No Interfaces Found\";\n";
    }
} else {
    $graph_text .= "\"No System Specified\";\n";
}

$graph_text .= "}\n";

$descriptor_spec = array(
    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
    2 => array("file", "/dev/null", "a") // stderr is a file to write to
);

$cmd = '/usr/bin/dot -Tsvg';

$process = proc_open($cmd, $descriptor_spec, $pipes, '/tmp');

if (is_resource($process)) {
    fwrite($pipes[0], $graph_text);
    fclose($pipes[0]);

    $graph = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    if ($return_value == 0) {
        ob_start();
        header("Content-type: image/svg+xml");
        ob_end_clean();
        session_write_close();
        echo $graph;
    }
}
