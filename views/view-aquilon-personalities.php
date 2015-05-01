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


function do_personalities($archetypes) {
    global $config;
    foreach ($archetypes as $archetype => $personalities) 
    {
        echo "  <div style='float: none; clear: both; position: relative; top: 60px;'><p class='cluster' style='font-size: 18pt; padding: 4px; text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);'>$archetype</p>\n";
        foreach ($personalities as $personality) {
            echo "  <div style=\"top: 0;\" class=\"cluster\" id=\"cl_{$archetype}_{$personality}\">\n";
            echo "      <p class=\"cluster\" title=\"Archetype: $archetype\nPersonality: $personality\">$personality</p>\n";
            $systems = file_get_contents($config['AQUILON']['URL'] . "find/host?personality=$personality&archetype=$archetype");
            do_systems($systems);
            echo "  </div>\n";
        }
        echo "  </div>\n";
    }
}


$personalities = Array();
$personality_info = file_get_contents($config['AQUILON']['URL'] . "personality?all");
$personality_info = explode("\n", $personality_info);
foreach ($personality_info as $line) {
    $l = explode(' ', trim($line));
    if ($l[0] == 'Host' and $l[1] == 'Personality:') {
        $personalities[$l[4]][] = $l[2];
    }
}



do_personalities($personalities);

?>