<?php
$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once("inc/config-call.inc.php");
require_once("inc/db-magdb-open.inc.php");
require_once("inc/ouilookup.inc.php");
require_once("inc/functions.inc.php");

$system = filter_input(INPUT_GET, 'system', FILTER_SANITIZE_NUMBER_INT);

$graph_text = "";

$DNS_CACHE_FILE = "cache/dns-aliases.json";

$dns = array();

if (file_exists($DNS_CACHE_FILE)) {
    $dns = file_get_contents($DNS_CACHE_FILE);
    $dns = str_replace("'", '"', $dns);
    $dns = json_decode($dns, True);
}

$graph_text .= "digraph \"aliases\" {\n";

$graph_text .= "graph [bgcolor=transparent];\n";
$graph_text .= "dpi=72;\n";
$graph_text .= "overlap=none;\n";
$graph_text .= "rankdir=\"LR\";\n";
$graph_text .= "concentrate=true;\n";
$graph_text .= "node [shape=\"box\" fontsize=8 fontname=\"sans-serif\" height=0 style=filled fillcolor=white width=1.6];\n";
$graph_text .= "edge [dir=none];\n";

if ($system) {
    $interfaces = pg_fetch_all(pg_query_params('select name, "macAddress", "isBootInterface" from "vNetworkInterfaces" where "systemId" = $1 order by name desc', Array($system)));
    $records = pg_fetch_all(pg_query_params('select "macAddress", "ipAddress", "fqdn", "alias" from "vNetwork3" where "systemId" = $1', Array($system)));
    $bonds = pg_fetch_all(pg_query_params('select "bondName", "macAddress", "lastUpdateDate" from "networkInterfaceBonds" where "systemId" = $1', Array($system)));
    $bond_details = pg_fetch_all(pg_query_params('select "bondName", "bondMode" from "networkInterfaceBondDetails" where "systemId" = $1', Array($system)));
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
        $count_ip = 0;
        if ($bonds) {
            foreach($bonds as $b) {
                $graph_text .= 'subgraph "cluster_'.$b["bondName"].'" {'."\n";
                $graph_text .= '    "'.$b["macAddress"].'"'."\n";
                $graph_text .= '    style="filled"'."\n";
                $graph_text .= '    color="#75507b"'."\n";
                $graph_text .= '    fillcolor="#ad7fa8"'."\n";
                $graph_text .= '    label="'.$b["bondName"].'"'."\n";
                $graph_text .= '}'."\n";
            }
        }
        if ($bond_details) {
            foreach($bond_details as $b) {
                $graph_text .= 'subgraph "cluster_'.$b["bondName"].'" {'."\n";
                $graph_text .= '    label="'.$b["bondName"].'\n'.$b["bondMode"]."\n";
                $graph_text .= '}'."\n";
            }
        }
        foreach ($interfaces as $i) {
            $style = "";
            if ($i["isBootInterface"] == "t") {
                $style = ' color="#204a87" fillcolor="#729fcf" tooltip="Bootable"';
            }
            $vendor = ouilookup($i["macAddress"]);
            $graph_text .= sprintf('"%s" [label="%s\n%s\n%s"%s];'."\n", $i["macAddress"], $i["name"], $i["macAddress"], $vendor, $style);

            $links = pg_fetch_all(pg_query_params('select "localMac", "localPort", "remoteMac", "remotePort", "remoteHost", "lastUpdateDate" from "networkLinks" where "localMac" = $1;', Array($i["macAddress"])));
            if ($links) {
                foreach ($links as $l) {
                    $graph_text .= 'subgraph "cluster_'.$l['remoteMac'].'"{'."\n";
                    $graph_text .= '  color="none"'."\n";
                    $lastUpdateDate = explode(" ", $l["lastUpdateDate"]);
                    $lastUpdateDate = $lastUpdateDate[0];
                    if ($l["localPort"] != "") {
                        $graph_text .= sprintf('"%s" [tooltip="LLDP Information" color="#8f5902" fillcolor="#e9b96e"];'."\n", $l["localPort"], $vendor);
                        $graph_text .= sprintf('"%s" -> "%s" -> "%s" [color="#8f5902"];'."\n", $l["remoteMac"], $l["localPort"], $l["localMac"]);
                    }
                    else {
                        $graph_text .= '"'.$l["remoteMac"].'" -> "'.$l["localMac"].'" [color="#8f5902"];'."\n";
                    }
                    $graph_text .= '"'.$l["remoteHost"].'" -> "'.$l["remoteMac"].'" [color="#8f5902"];'."\n";
                    $graph_text .= '}'."\n";
                    $vendor = ouilookup($l["remoteMac"]);
                    $graph_text .= sprintf('"%s" [label="%s\n%s\n%s" tooltip="LLDP Information" color="#8f5902" fillcolor="#e9b96e"];'."\n", $l["remoteMac"], $l["remotePort"], $l["remoteMac"], $vendor);
                    $graph_text .= sprintf(
                        '"%s" [label="%s\nObserved %s" color="#8f5902" tooltip="LLDP Information" fillcolor="#e9b96e" URL="/node.php?n=%s" target="_parent"];'."\n",
                        $l["remoteHost"], $l["remoteHost"], $lastUpdateDate, $l['remoteHost']
                    );
                }
            }
            else {
                $unknown_switch = 'UnknownSwitchFor'.$i["macAddress"];
                $unknown_port = 'UnknownPortFor'.$i["macAddress"];
                $graph_text .= '"'.$unknown_switch.'" [label="Unknown Switch" style="dashed" color="#888a85"];'."\n";
                $graph_text .= '"'.$unknown_port.'" [label="Unknown Port" style="dashed" color="#888a85"];'."\n";
                $graph_text .= '"'.$unknown_switch.'" -> "'.$unknown_port.'" [style="dashed" color="#888a85"];'."\n";
                $graph_text .= '"'.$unknown_port.'" -> "'.$i["macAddress"].'" [style="dashed" color="#888a85"];'."\n";
            }
        }
        foreach ($records as $r) {
            $graph_text .= '"'.$r["macAddress"].'" -> "'.$r["ipAddress"].'";'."\n";
            $seen = "Never Seen";
            $count_ip += 1;
            if (isset($lastseen[$r["ipAddress"]])) {
                $seen = $lastseen[$r["ipAddress"]];
                if ($seen <= 86400) {
                    $seen = "Last Seen Today";
                } else {
                    $seen = 'Last Seen '.prettytime($seen);
                }
            }
            $graph_text .= '"'.$r["ipAddress"].'" [label="'.$r["ipAddress"].'\n '.$seen.'" URL="http://'.$r["ipAddress"].'" target="_parent"];'."\n";
            if ($r["fqdn"]) {
                $graph_text .= '"'.$r["fqdn"].'" [URL="/node.php?n='.$r["fqdn"].'" target="_parent"];'."\n";
                $graph_text .= '"'.$r["ipAddress"].'" -> "'.$r["fqdn"].'";'."\n";
                if (is_array($dns) and array_key_exists($r["fqdn"], $dns)) {
                    foreach ($dns[$r["fqdn"]] as $a) {
                        $graph_text .= '"'.$a[3].'" [label="'.$a[3].'\n'.$a[0].'  '.$a[1].'  '.$a[2].'" color="#555753" fillcolor="#d3d7cf"]'."\n";
                        $graph_text .= '"'.$r["fqdn"].'" -> "'.$a[3].'";'."\n";
                    }
                }
            }
        }
    } else {
        $graph_text .= "\"No Interfaces Found\";\n";
    }
} else {
    $graph_text .= "\"No System Specified\";\n";
}

$graph_text .= "}\n";

$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
    2 => array("file", "/dev/null", "a") // stderr is a file to write to
);

$cmd = ' /usr/bin/dot -Tsvg';

if ($count_ip > 4) {
    $cmd = "unflatten -l 5 -f | $cmd";
}

$process = proc_open($cmd, $descriptorspec, $pipes, '/tmp');

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
