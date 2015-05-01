<?php
//Important includes
require("header.php");
$config = parse_ini_file("config/config.ini", true);

function do_node($node) {
    $short = explode('.', $node);
    $short = $short[0];

    $mynode  = mysql_query("select note from nodelist LEFT JOIN notes on (notes.name=nodelist.name) where name=$short ORDER BY layer;");
    if ($mynode and mysql_num_rows($mynode)) {
        $nodecsf = mysql_fetch_row($mynode);
    }
    else {
        $nodecsf = null;
    }

    //Set defaults
    $nodeStatus = "unknown";
    $nodeNote   = "";
    $nodeInfo   = "<h4>$node</h4>";

    $nodeNote = $nodecsf[0];

    $ntup = nagios_state($short, $node, $nodeStatus);
    if ($ntup[1]) {
        $nodeStatus = $ntup[0];
        $nodeInfo .= "<p><b>Nagios:</b> {$ntup[1]}</p>";
    }
    unset($ntup);

    //Process notes
    if ($nodeNote != "") {
      //Tack note onto end of info string
      $nodeInfo .= ' - '.$nodeNote;

      //We want to be case insensitive!
      $s = strtolower($nodeNote);
      $nodeStatus .= ' note';
    }

    echo '      <span id="n_'.$short.'" onclick="node(\''.$node."')\" class=\"node $nodeStatus\" title=\"".htmlentities($nodeInfo).'"></span>'."\n";
}

function do_systems($systems) {
    if ($systems) {
        $systems = explode("\n", trim($systems));
        foreach ($systems as $s) {
            do_node($s);
        }
    }
    else {
        echo "      <span title='No managed systems'>&nbsp;&#x2205;</span>";
    }
}


function do_domains($domains) {
    global $config;
    echo "<div class=\"cluster\" style=\"width: auto\" id=\"cl_domains\">\n";
    echo "  <p class=\"cluster\">Domains</p>\n";
    foreach ($domains as $domain) {
        echo "  <div class=\"diskpool\" id=\"dp_$domain\">\n";
        echo "    <p class=\"diskpool\">$domain</p>\n";
        $systems = file_get_contents($config['AQUILON']['URL'] . "find/host?domain=$domain");
        do_systems($systems);
        echo "  </div>\n";
    }
    echo "</div>\n";
}


function do_sandboxes($boxen) {
    global $config;
    $sandboxes = Array();
    $sandboxname = '';
    $realnames = Array();

    foreach ($boxen as $l) {
        $l = explode(' ', trim($l));

        if ($l[0] == 'Sandbox:') {
            $sandboxname = $l[1];
        }
        elseif ($l[0] == 'Owner:') {
            $owner = $l[1];
            if (!array_key_exists($owner, $realnames)) {
                $realname = exec("/usr/local/bin/federal_id.py $owner");
                $realname = explode(',', $realname);
                $realname = $realname[0];
                if (! $realname) {
                    $realname = $owner;
                }
                $realnames[$owner] = $realname;
            }
            if (!array_key_exists($owner, $sandboxes)) {
                $sandboxes{$owner} = Array();
            }
            $sandboxes{$owner}[] = $sandboxname;
            $sandboxname = '';
        }
    }

    asort($sandboxes);

    foreach ($sandboxes as $user => $boxes) {
        echo "<div class=\"cluster\" id=\"cl_$user\">\n";
        echo "  <p class=\"cluster\" title=\"$user\">{$realnames[$user]}</p>\n";
        foreach ($boxes as $box) {
            echo "  <div class=\"diskpool\" id=\"dp_$box\">\n";
            echo "    <p class=\"diskpool\" title=\"$user/$box\">$box</p>\n";
            $systems = file_get_contents($config['AQUILON']['URL'] . "find/host?sandbox=$user/$box");
            do_systems($systems);
            echo "  </div>\n";
        }
        echo "</div>\n";
    }
}


$boxen = file_get_contents($config['AQUILON']['URL'] . "sandbox/command/show_all");
$boxen = explode("\n", $boxen);

$domains = Array();
$domain_info = file_get_contents($config['AQUILON']['URL'] . "domain?all");
$domain_info = explode("\n", $domain_info);
foreach ($domain_info as $line) {
    $l = explode(' ', trim($line));
    if ($l[0] == 'Domain:') {
        $domains[] = $l[1];
    }
}

echo "<div>\n";
do_domains($domains);
echo "</div>\n";

echo "<div style=\"clear: both\">\n";
do_sandboxes($boxen);
echo "</div>\n";

?>
