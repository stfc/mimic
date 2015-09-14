<?php

class vminfomation {

    function header($node) {
        $header = Array("SCD Cloud");
        return($header);
    }

    function detail($node) {
        $ip_addr = gethostbyname($node);

        // Gets data about all VMs
        $vmxml = shell_exec('/usr/bin/python xmlrpc/vmpoolinfo.py');
        $vmpoolinfo = simplexml_load_string($vmxml, null, LIBXML_NOCDATA);

        // Organises data into main array
        $results = Array();

        // VM only
        if (strpos($node, 'vm') !== false) {

            // Gets list of templates
            $tmoutput = shell_exec('/usr/bin/python xmlrpc/templatepoolinfo.py');
            $tmxml = simplexml_load_string($tmoutput, null, LIBXML_NOCDATA);

            $templates = Array();
            foreach ($tmxml as $val) {
                $temp_id = (string) $val->ID;
                $templates[$temp_id] = (string) $val->NAME;
            }

            // Array of VM info
            foreach ($vmpoolinfo as $host) {
                $vmip = (string) $host->TEMPLATE->NIC->IP;

                if ($ip_addr == $vmip) {
                    $results['VM ID'] = (string) $host->TEMPLATE->CONTEXT->VMID;
                    $results['Date Created'] = date('l dS \o\f F Y h:i:s A', (string) $host->STIME);
                    $results['User'] = (string) $host->UNAME;
                    $results['Group'] = (string) $host->GNAME;
                    $results['Memory'] = (string) $host->MEMORY." MB";
                    $results['CPU'] = (string) $host->TEMPLATE->CPU;
                    $results['Used CPU'] = (string) $host->CPU;
                    $results['VCPU'] = (string) $host->TEMPLATE->VCPU;
                    $results['Hypervisor'] = (string) $host->HISTORY_RECORDS->HISTORY->HOSTNAME;

                    $template_id = (string) $host->TEMPLATE->TEMPLATE_ID;
                    if (array_key_exists($template_id, $templates)) {
                        $results['Template'] = $templates[$template_id];
                    }

                    foreach ($host->TEMPLATE->DISK as $disk) {
                        $disk_id = "Disk ".$disk->DISK_ID;
                        $results[$disk_id]['Disk ID'] = (string) $disk->DISK_ID;
                        $results[$disk_id]['Image'] = (string) $disk->IMAGE;
                        $results[$disk_id]['Snapshot ID'] = (string) $disk->SAVE_AS;
                        $results[$disk_id]['Size'] = (string) $disk->SIZE." MB";
                    }
                    break;
                }
            }
        }

        // Hypervisor only
        elseif (strpos($node,'hv') !== false) {
            // Gets data about all Hypervisors
            $hvxml = shell_exec('/usr/bin/python xmlrpc/hostpoolinfo.py');
            $hvpoolinfo = simplexml_load_string($hvxml, null, LIBXML_NOCDATA);

            // Makes array of all VMs in a Hypervisor
            $hyper = Array();
            foreach ($vmpoolinfo as $value) {
                $hv_name = (string) $value->HISTORY_RECORDS->HISTORY->HOSTNAME;
                $hvip = gethostbyaddr((string) $value->TEMPLATE->NIC->IP);
                $hyper[$hv_name][] = "<a href='node.php?n=$hvip'>".$hvip."</a>";
            }

            // Array of Hypervisor info
            foreach ($hvpoolinfo as $host) {
                $hvname = (string) $host->NAME;

                if ($node == $hvname) {
                    $results['Name'] = $hvname;
                    $results['State'] = (string) $host->STATE;
                    $results['Running VMs'] = (string) $host->HOST_SHARE->RUNNING_VMS;

                    $results['CPU']['Allocated'] = (string) $host->HOST_SHARE->CPU_USAGE;
                    $results['CPU']['Maximum'] = (string) $host->HOST_SHARE->MAX_CPU;
                    $results['CPU']['Free'] = (string) $host->HOST_SHARE->FREE_CPU;
                    $results['CPU']['Used'] = (string) $host->HOST_SHARE->USED_CPU;

                    $results['Memory']['Allocated'] = (string) $host->HOST_SHARE->MEM_USAGE." MB";
                    $results['Memory']['Maximum'] = (string) $host->HOST_SHARE->MAX_MEM." MB";
                    $results['Memory']['Free'] = (string) $host->HOST_SHARE->FREE_MEM." MB";
                    $results['Memory']['Used'] = (string) $host->HOST_SHARE->USED_MEM." MB";
                    if (array_key_exists($hvname, $hyper)) {
                        $results['VMs'] = $hyper[$hvname];
                    }
                    break;
                }
            }
        }

        // Renders data
        if (!empty($results)) {
            echo "<ul>";
            foreach ($results as $info => $val) {
                if (is_array($val)) {
                    echo "<li><strong>$info:</strong></li>";
                    echo "<ul>";
                    foreach ($val as $k => $v) {
                        echo "<li><strong>$k</strong> &ndash; $v</li>";
                    }
                    echo "</ul>";
                }
                else {
                    echo "<li><strong>$info</strong> &ndash; $val</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p class='info'>No info for host.</p>";
        }
    }
}

return new vminfomation();
