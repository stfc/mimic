<?php

include("config-call.inc.php");

$NETBOX_API_URL = $CONFIG['URL']['NETBOX'] . "api";
$NETBOX_URL = $CONFIG['URL']['NETBOX'];
$NETBOX_TOKEN = $CONFIG['NETBOX']['TOKEN'];

class netbox
{
    function json_query($URL, $TOKEN=False)
    {
        global $NETBOX_TOKEN;
        if ($TOKEN === False) {
            $TOKEN = $NETBOX_TOKEN;
        }
        $url = curl_init($URL);
        curl_setopt($url, CURLOPT_CONNECTTIMEOUT,5);
        curl_setopt($url, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($url, CURLOPT_CAPATH,"/etc/grid-security/certificates");
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, true);
        if ($TOKEN) {
            curl_setopt($url, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $TOKEN));
        }
        $out = curl_exec($url);
        if (curl_error($url)) {
            echo curl_error($url);
            echo "curl error";
            return null;
        }
        curl_close($url);
        return json_decode($out, true);
    }

    function query($PATH,$ARGS)
    {
        global $NETBOX_API_URL;

        $json_out = $this->json_query($NETBOX_API_URL . $PATH . $ARGS);

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
    function get_info($machineName)
    {
        // First look for a named device
        $q1=$this->search("/dcim/devices/", array("name"=>$machineName));
        if ($q1 == null) {
            //If that fails, then search by DNS name
            $q2=$this->search("/ipam/ip-addresses/", array("dns_name"=>$machineName));
            if ($q2 == null) {
                return null;
            } else if (count($q2) > 1) {
                echo "Got multiple results from Netbox!";
                return null;
            } else {
                $assigned_object = $q2[0]["assigned_object"];
                if (is_array($assigned_object)) {
                    if (array_key_exists('device', $assigned_object)) {
                        return $this->json_query($assigned_object["device"]["url"]);
                    } else if (array_key_exists('virtual_machine', $assigned_object)) {
                        return $this->json_query($assigned_object["virtual_machine"]["url"]);
                    } else {
                        return Array(
                            'error' => 'Don\'t know how to handle the assigned object',
                            'assigned_object_type' => $q2[0]["assigned_object_type"],
                        );
                    }
                } else {
                    return Array(
                        'error' => 'Stub Record - No object assigned to IP',
                        'address' => $q2[0]['address'],
                    );
                }
            }
        } else if (count($q1) > 1) {
            echo "Got multiple results from Netbox!";
            return null;
        } else {
            return $q1[0];
        }
    }

    // Get Netbox info using netbox id
    function get_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->query("/dcim/devices/", $id . "/");
    }

    function get_powerfeed_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->query("/dcim/power-feeds/", $id . "/");
    }

    function get_powerpanel_info_by_id($id) {
        // Trailing / required to avoid redirecting
        return $this->query("/dcim/power-panels/", $id . "/");
    }

    // Run a generic Netbox query against an API path,
    // Takes an array of search terms
    function search($path,$params) {
        return $this->query($path, '?' .  http_build_query($params));
    }
}
