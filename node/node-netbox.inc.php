<?php

include("inc/config-call.inc.php");

$NETBOX_API_URL = $CONFIG['URL']['NETBOX'] . "api";
$NETBOX_URL = $CONFIG['URL']['NETBOX'];
$NETBOX_TOKEN = $CONFIG['NETBOX']['TOKEN'];

class pNetbox
{
    function header($NODE, $SHORT)
    {
        return("Netbox");
    }

    private function netbox_query($PATH,$ARGS)
    {
        global $NETBOX_API_URL, $NETBOX_TOKEN;

        $url = curl_init($NETBOX_API_URL . $PATH . $ARGS);
        curl_setopt($url, CURLOPT_CONNECTTIMEOUT,5);
        curl_setopt($url, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($url, CURLOPT_CAPATH,"/etc/grid-security/certificates");
        curl_setopt($url, CURLOPT_HTTPHEADER, array('Authorization: Token '.$NETBOX_TOKEN));
        $out = curl_exec($url);
        if (curl_error($url)) {
            echo curl_error($url);
            echo "curl error";
            return null;
        }
        curl_close($url);
		
        $json_out = json_decode($out,true);

        // Do not have a count value or a netbox id - something is wrong
        if (!isset($json_out['count']) && !(isset($json_out['id']))) {
            return null;
        }
		
        // We have an id so just return the json object as it was a direct request
        if (isset($json_out['id'])) {
            return $json_out;
        }
		
        if (isset($json_out['count'])) {
            if ($json_out['count'] == 0) {
                // Search returned no values so return null
                return null;
            } else {
                // Search returned multiple values so return the array
                return $json_out['results'];
            }
        }
    }
	
    // Get Netbox info for a single machine
    // To the Netbox API this is technically a search
    // So be careful we only get one response back	
    private function get_netbox_info($machineName)
    {

        $query=$this->netbox_search("/dcim/devices/",array("name"=>$machineName));
        if ($query == null) {
            return null;
        } else if (count($query) > 1) {
            echo "Got multiple results from Netbox!";
            return null;
        } else {
            return $query[0];
        }	
    }
    
    // Get Netbox info using netbox id
    private function get_netbox_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->netbox_query("/dcim/devices/", $id . "/");
    }

    private function get_netbox_powerfeed_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->netbox_query("/dcim/power-feeds/", $id . "/");
    }

    private function get_netbox_powerpanel_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->netbox_query("/dcim/power-panels/", $id . "/");
    }
		
    // Run a generic Netbox query against an API path,
    // Takes an array of search terms	
    private function netbox_search($path,$params) {
        return $this->netbox_query($path, '?' .  http_build_query($params));
    }
			
    private function render_pdu_list($rack_pdus)
    {
        echo "<h3>Rack Power</h3>\n";
		
        if ($rack_pdus != null) {
            echo "<dl>\n";
            foreach ($rack_pdus as $rack_pdu) {
                $conns=$this->netbox_search("/dcim/power-ports/", array("device"=>$rack_pdu['name']));		
                if ($conns != null) {
                    // For situation where downstream power ports are configured, but upstream is not
                    $found_supply=false;
                    foreach ($conns as $conn) {
                        // If the connected endpoint is dcim.powerfeed then its the power supply
                        if ($conn['connected_endpoint_type'] == "dcim.powerfeed") {
                            $found_supply=true;
                            $power_feed=$this->get_netbox_powerfeed_info_by_id($conn['connected_endpoint']['id']);
                            $room_pdu=$this->get_netbox_powerpanel_info_by_id($power_feed['power_panel']['id']);
                            printf("<dt>%s (%s)</dt><dd>%s</dd>\n", $conn['connected_endpoint']['name'], $rack_pdu['name'], $room_pdu['site']['name']);
                        } 
                    }
                    if ($found_supply == false) {
                        printf("<dt>PDU providing power but no supply (%s)</dt><dd>Unknown</dd>\n", $rack_pdu['name']);	
                    }
                } else {
                    printf("<dt>PDU with no supply and not supplying power (%s)</dt><dd>Unknown</dd>\n", $rack_pdu['name']);
                }		
            }
            echo "</dl>\n";
        } else {
            echo "<p class=\"warning\">No rack power information.</p>\n";
        }
    }

    function detail($NODE, $SHORT)
    {
        global $NETBOX_URL;

        $netbox_info = $this->get_netbox_info($NODE);
				
        if ($netbox_info !== null) {
			
            // Determine Rack Position
            // If not set in device, check the parent device if it exists
			
            if ($netbox_info['position'] != null) {
                $rackpos = $netbox_info['position'];
            } else if ($netbox_info['position'] == null && $netbox_info['parent_device'] != null) {
				
                $netbox_parent = $this->get_netbox_info_by_id($netbox_info['parent_device']['id']);
				
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
            echo "<dt>NetboxId</dt><dd class=\"netbox-id\"><a href=\"" . $NETBOX_URL."dcim/devices/". $netbox_info['id'] . "\" title=\"View device ".$netbox_info['id']." in Netbox\">". $netbox_info['id']."</a></dd>\n";
            echo "<dt>IpAddress</dt><dd class=\"netbox-ipaddress\">" . $netbox_info['primary_ip']['address'] . "</dd>\n";
            echo "<dt>roomName</dt><dd class=\"netbox-roomname\">" . $netbox_info['site']['name'] . "</dd>\n";
            echo "<dt>rackId</dt><dd class=\"netbox-rackid\"><a href=\"" . $NETBOX_URL . "dcim/racks/" . $netbox_info['rack']['id'] . "\" title=\"View rack ".$netbox_info['rack']['display']." in Netbox\">" . $netbox_info['rack']['display'] . "</a></dd>\n";
            echo "<dt>systemRackPos</dt><dd class=\"netbox-RackPos\">$rackpos</dd>\n";
            echo "<dt>deviceType</dt><dd class=\"netbox-categoryName\">" . $netbox_info['device_type']['model']."</dd>\n";
            echo "<dt>vendorName</dt><dd class=\"netbox-vendorName\">" . $netbox_info['device_type']['manufacturer']['name'] . "</dd>\n";

            if ($netbox_info['serial'] != null) {
                echo "<dt>Serial</dt><dd class=\"netbox-serial\">" . $netbox_info['serial'] . "</dd>\n";	
            }
            echo "<dt>Status</dt><dd class=\"netbox-status\">" . $netbox_info['status']['label'] . "</dd>\n";
			
            echo "</dl>\n";

            $rack_pdus = $this->netbox_search("/dcim/devices/", array("rack_id"=>$netbox_info['rack']['id'],"role"=>"pdu"));
            $this->render_pdu_list($rack_pdus);	
			
        } else {
            echo "<p class=\"warning\">Host not in Netbox.</p>\n";
        }
    }
}

return new pNetbox();
