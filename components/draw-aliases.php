<?php

require("inc/db-magdb-open.inc.php");

$host = filter_input(INPUT_GET, 'host', FILTER_SANITIZE_STRING);

$graph_text = "";
$graph_text .= "digraph \"aliases\" {\n";
$graph_text .= "graph [bgcolor=transparent];";
$graph_text .= "dpi=72;";
$graph_text .= "overlap=none;";
$graph_text .= "node [shape=\"box\" fontsize=10 height=0 style=filled fillcolor=white];";

$aliases = pg_fetch_all(pg_query('select "host" || \'.\' || "hostDomain" as "tgt", "alias" || \'.\' || "aliasDomian" as "src" from "vAliases" where "host" = \''.$host.'\' or "alias" = \''.$host.'\''));

if ($aliases) {
    foreach ($aliases as $r) {
        $graph_text .= "\"".$r["src"]."\" -> \"".$r["tgt"]."\";\n";
    }
} else {
    $graph_text .= "\"No Aliases Found\";\n";
}

$graph_text .= "}\n";

$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
    2 => array("file", "/dev/null", "a") // stderr is a file to write to
);

$cwd = '/tmp';
$env = array('some_option' => 'aeiou');

$process = proc_open('/usr/bin/dot -Tpng', $descriptorspec, $pipes, $cwd, $env);

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
        header("Content-type: image/png");
        ob_end_clean();
        session_write_close();
        echo $graph;
    }
}
