<?php
$path = '/var/www/html/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once("inc/db-magdb-open.inc.php");
require_once("inc/ouilookup.inc.php");
require_once("inc/functions.inc.php");

$system = "793";

if (isset($_REQUEST["system"])) {
  $system = $_REQUEST["system"];
}

$graph_text = "";

$DNS_CACHE_FILE = "../cache/dns-aliases.json";

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
  $interfaces = pg_fetch_all(pg_query('select "name", "macAddress", "isBootInterface" from "vNetworkInterfaces" where "systemId" = '.$system.' order by "name" desc'));
  $records = pg_fetch_all(pg_query('select "macAddress", "ipAddress", "fqdn", "alias" from "vNetwork3" where "systemId" = '.$system));
  // Last Seen
  $ls = pg_fetch_all(pg_query('select "ipAddress", EXTRACT(EPOCH FROM now() - "lastSeen") as "lastSeen", date_trunc(\'day\', "lastSeen") = date_trunc(\'day\', now()) as "today" from "ipSurvey"'));
  $lastseen = Array();
  foreach ($ls as $l) {
    if(!isset($lastseen[$l["ipAddress"]])){
      $lastseen[$l["ipAddress"]] = $l["lastSeen"];
    }
  }

  if ($interfaces or $records) {
    $count_ip = 0;
    foreach ($interfaces as $i) {
      $style = "";
      if ($i["isBootInterface"] == "t") {
        $style = ' color="#204a87" fillcolor="#729fcf" tooltip="Bootable"';
      }
      $v = ouilookup($i["macAddress"]);
      $graph_text .= sprintf('"%s" [label="%s\n%s\n%s"%s];'."\n", $i["macAddress"], $i["name"], $i["macAddress"], $v, $style);
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

?>
