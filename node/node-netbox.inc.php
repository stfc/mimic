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

            // Determine Rack Position
            // If not set in device, check the parent device if it exists

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

            echo "<h3>System</h3>\n";
            echo "<dl>\n";
            echo "<dt>Netbox ID</dt>";
            echo "<dd><a href=\"" . str_replace('/api/', '/', $netbox_info['url']) . "\" title=\"View device ".$netbox_info['id']." in Netbox\">". $netbox_info['id']."</a></dd>\n";
            echo "<dt>IpAddress</dt><dd class=\"netbox-ipaddress\">" . $netbox_info['primary_ip']['address'] . "</dd>\n";
            echo "<dt>roomName</dt><dd class=\"netbox-roomname\">" . $netbox_info['site']['name'] . "</dd>\n";
            echo "<dt>rackId</dt><dd class=\"netbox-rackid\"><a href=\"" . $NETBOX_URL . "dcim/racks/" . $netbox_info['rack']['id'] . "\" title=\"View rack ".$netbox_info['rack']['display']." in Netbox\">" . $netbox_info['rack']['display'] . "</a></dd>\n";
            echo "<dt>systemRackPos</dt><dd class=\"netbox-RackPos\">$rackpos</dd>\n";
            echo "<dt>deviceType</dt><dd class=\"netbox-categoryName\">" . $netbox_info['device_type']['model']."</dd>\n";

            if ($netbox_info['serial'] != null) {
                echo "<dt>Serial</dt><dd class=\"netbox-serial\">" . $netbox_info['serial'] . "</dd>\n";
            }
            echo "<dt>Status</dt><dd class=\"netbox-status\">" . $netbox_info['status']['label'] . "</dd>\n";

            if (array_key_exists('device_type', $netbox_info)) {
                echo "<dt>vendorName</dt><dd>" . $netbox_info['device_type']['manufacturer']['name'] . "</dd>\n";
            }

            echo "</dl>\n";

            if (array_key_exists('rack', $netbox_info) && is_array($netbox_info['rack'])) {
                $rack_pdus = $this->netbox->search("/dcim/devices/", array("rack_id"=>$netbox_info['rack']['id'],"role"=>"pdu"));
                $this->render_pdu_list($rack_pdus);
            }
        } else {
            echo "<p class=\"warning\">Host not in Netbox.</p>\n";
        }
    }
}

return new pNetbox();
