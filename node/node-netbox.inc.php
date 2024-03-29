<?php

include("inc/config-call.inc.php");

require_once("inc/ds-netbox.inc.php");

class pNetbox
{
    function __construct() {
        $this->netbox = new netbox();
    }

    function header($NODE, $SHORT)
    {
        return("Netbox");
    }

    private function render_pdu_list($rack_pdus)
    {
        echo "<h3>Rack Power</h3>\n";

        $icon_table = Array(
            'powered'   => '<span title="Powered">&#x026A1;</span>',
            'connected' => '<span title="Feed Defined">&#x1F50C;</span>',
            'ups'       => '<span title="UPS Backed">&#x1f50B;</span>',
            'null'      => '&mdash;',
        );

        if ($rack_pdus != null) {
            echo "<dl>\n";
            foreach ($rack_pdus as $rack_pdu) {
                $rack_pdu_link = sprintf("<a href=\"%s\">%s</a>", str_replace('/api/', '/', $rack_pdu['url']), $rack_pdu['name']);
                $conns=$this->netbox->search("/dcim/power-ports/", array("device"=>$rack_pdu['name']));
                $supply_text="Unknown";
                if ($conns != null) {
                    // For situation where downstream power ports are configured, but upstream is not
                    $found_supply=false;
                    foreach ($conns as $conn) {
                        // If the connected endpoint is dcim.powerfeed then its the power supply
                        if ($conn['connected_endpoints_type'] == "dcim.powerfeed") {
                            $found_supply=true;
                            foreach ($conn['connected_endpoints'] as $endpoint) {
                                $power_feed=$this->netbox->get_powerfeed_info_by_id($endpoint['id']);
                                $room_pdu=$this->netbox->get_powerpanel_info_by_id($power_feed['power_panel']['id']);

                                $supply_text=sprintf("<a href=\"%s\">%s</a> (%s)", str_replace('/api/', '/', $endpoint['url']), $endpoint['name'], $room_pdu['site']['name']);

                                $icons = $icon_table['powered'];
                                if ($room_pdu['custom_fields']['ups_backed']) {
                                    $icons .= $icon_table['ups'];
                                } else {
                                    $icons .= $icon_table['null'];
                                }
                                $icons .= $icon_table['connected'];

                                printf("<dt>%s</dt><dd>&#x02500;%s&rarr; %s</dd>\n", $supply_text, $icons, $rack_pdu_link);
                            }
                        }
                    }
                    if ($found_supply == false) {
                        // Power to rack, but no supply defined
                        $icons = $icon_table['powered'] . $icon_table['null'] . $icon_table['null'];
                        printf("<dt>%s</dt><dd>&#x02500;%s&rarr; %s</dd>\n", $supply_text, $icons, $rack_pdu_link);
                    }
                } else {
                    // No power to rack and no supply defined
                    $icons = $icon_table['null'] . $icon_table['null'] . $icon_table['null'];
                    printf("<dt>%s</dt><dd>&#x02500;%s&rarr; %s</dd>\n", $supply_text, $icons, $rack_pdu_link);
                }
            }
            echo "</dl>\n";
        } else {
            echo "<p class=\"warning\">No rack power information.</p>\n";
        }
    }

    private function render_networking($device_id, $virtual=false)
    {
        echo "<h3>Networking</h3>\n";
        echo "<object ";
        //Add work-around for webkit's broken SVG embedding.
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "webkit")) {
            echo 'style="width: 100%;" ';
        }
        echo "type=\"image/svg+xml\" data=\"/components/netbox-draw-interfaces.php?netbox_id={$device_id}&is_vm={$virtual}\"></object>\n";
    }

    function detail($NODE, $SHORT)
    {
        $netbox_info = $this->netbox->get_info($NODE);

        if ($netbox_info !== null) {

            if (array_key_exists('error', $netbox_info)) {
                $error_message = $netbox_info['error'];
                unset($netbox_info['error']);
                echo "<dl>\n";
                foreach ($netbox_info as $k => $v) {
                    echo "<dt>$k</dt><dd>$v</dd>\n";
                }
                echo "</dl>\n";
                echo "<p class=\"error\">{$error_message}</p>\n";
                return;
            }

            $is_vm = (strpos($netbox_info['url'], 'virtualization/virtual-machines') !== false);

            // Determine Rack Position
            // If not set in device, check the parent device if it exists

            $rackpos=false;
            if (array_key_exists('position', $netbox_info)) {
                if ($netbox_info['position'] != null) {
                    $rackpos = $netbox_info['position'];
                } else if ($netbox_info['position'] == null && $netbox_info['parent_device'] != null) {
                    $netbox_parent = $this->netbox->get_info_by_id($netbox_info['parent_device']['id']);

                    if ($netbox_parent['position'] != null) {
                        $rackpos=$netbox_parent['position'] . " (Child of ". $netbox_info['parent_device']['display'] . ")";
                    } else {
                        $rackpos="No position in parent";
                    }
                } else {
                    $rackpos="No position";
                }
            }

            echo "<h3>System</h3>\n";
            echo "<dl>\n";
            echo "<dt>Netbox ID</dt>";
            echo "<dd><a href=\"" . str_replace('/api/', '/', $netbox_info['url']) . "\" title=\"View device ".$netbox_info['id']." in Netbox\">". $netbox_info['id']."</a></dd>\n";

            if ($rackpos) {
                echo "<dt>Rack Position</dt><dd class=\"netbox-RackPos\">$rackpos</dd>\n";
            }

            // Map of Field name => Label when rendered
            $fields_to_display = Array(
                'rack' => 'Rack',
                'site' => 'Room',
                'primary_ip4' => 'Primary IPv4',
                'primary_ip6' => 'Primary IPv6',
                'device_type' => 'Device Type',
                'serial' => 'Serial',
                'tenant' => 'Tenant',
                'cluster' => 'Virtual Cluster',
                'device_role' => 'Role',
                'role' => 'Role',
                'status' => 'Status',
                'platform' => 'Platform',
                'vcpus' => 'Virtual CPUs',
                'memory' => 'Memory (MB)',
                'disk' => 'Disk (GB)',
                'comments' => 'Comments',
                'tags' => 'Tags',
            );

            foreach($fields_to_display as $field_name => $field_label) {
                if (array_key_exists($field_name, $netbox_info) && $netbox_info[$field_name] != null) {
                    $field_value = $netbox_info[$field_name];
                    $link = False;
                    if (is_array($field_value)) {
                        if (array_key_exists('url', $field_value)) {
                            $link = str_replace('/api/', '/', $field_value['url']);
                        }
                        if (array_key_exists('display', $field_value)) {
                            $field_value = $field_value['display'];
                        } else if (array_key_exists('label', $field_value)) {
                            $field_value = $field_value['label'];
                        } else if (is_array($field_value)) {
                            $s = Array();
                            foreach($field_value as $k => $v) {
                                $style="padding: .35em .65em; border-radius: .375rem; ";
                                if (array_key_exists('color', $v)) {
                                   $style.=" background-color: #{$v['color']};";
                                };
                                $s[] = "<li style=\"$style\">{$v['display']}</li>";
                            }
                            $field_value = "<ul style=\"list-style: none;\">" . implode("\n", $s) . "</ul>";
                        }
                    }
                    if ($link) {
                        $field_value = "<a href=\"$link\">$field_value</a>\n";
                    }
                    echo "<dt>$field_label</dt><dd>$field_value</dd>\n";
                }
            }

            if (array_key_exists('device_type', $netbox_info)) {
                echo "<dt>vendorName</dt><dd>" . $netbox_info['device_type']['manufacturer']['name'] . "</dd>\n";
            }

            echo "<dt>Custom Fields</dt><dd><dl>\n";
            foreach($netbox_info['custom_fields'] as $field_name => $field_value) {
                if ($field_value) {
                    echo "<dt>$field_name</dt><dd>$field_value</dd>\n";
                }
            }
            echo "</dl></dd>\n";

            echo "</dl>\n";

            if (array_key_exists('rack', $netbox_info) && is_array($netbox_info['rack'])) {
                $rack_pdus = $this->netbox->search("/dcim/devices/", array("rack_id"=>$netbox_info['rack']['id'],"role"=>"pdu"));
                $this->render_pdu_list($rack_pdus);
            }

            $this->render_networking($netbox_info['id'], $is_vm);

        } else {
            echo "<p class=\"warning\">Host not in Netbox.</p>\n";
        }
    }
}

return new pNetbox();
