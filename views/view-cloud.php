<?php
require("header.php"); // Important includes

$archetype = 'ral-tier1';

// Generate list of VMs that are in a resting state (i.e. correct personality and in prod)
$vms_all = file_get_contents($CONFIG['AQUILON']['URL'] . "find/host?dns_domain=" . $CONFIG['NUBES']['URL']);
$vms_all = explode("\n", $vms_all);
$vms_good = file_get_contents($CONFIG['AQUILON']['URL'] . "find/host?dns_domain=" . $CONFIG['NUBES']['URL'] . "&personality=nubesvms&domain=prod");
$vms_good = explode("\n", $vms_good);
$vms_bad = array_diff($vms_all, $vms_good);
unset($vms_all);

function do_node($node) {

    global $vms_good;
    global $vms_bad;

    $short = explode('.', $node);
    $short = $short[0];

    $mynode  = mysql_query("select note from nodelist LEFT JOIN notes on (notes.name=nodelist.name) where name=$short ORDER BY layer;");
    if ($mynode and mysql_num_rows($mynode)) {
        $nodecsf = mysql_fetch_row($mynode);
    }
    else {
        $nodecsf = null;
    }

    $nodeNote = "";
    $nodeInfo = "<h4>$node</h4>";

    // Set defaults
    $nodeStatus = "unknown";
    if (in_array($node, $vms_bad)) {
        $nodeStatus = 'cloud-bad';
        $nodeInfo .= "<p><b>Warning:</b> VM host not in prod or personality not nubesvms, will need to be cleaned up after deletion.</p>";
    }

    $nodeNote = $nodecsf[0];
    $ntup = nagios_state($short, $node, $nodeStatus);
    if ($ntup[1]) {
        $nodeStatus = $ntup[0];
        $nodeInfo .= "<p><b>Nagios:</b> {$ntup[1]}</p>";
    }
    unset($ntup);

    // Process notes
    if ($nodeNote != "") {
        // Tack note onto end of info string
        $nodeInfo .= ' - '.$nodeNote;

        //We want to be case insensitive!
        $s = strtolower($nodeNote);
        $nodeStatus .= ' note';
    }

    echo '<span id="n_'.$short.'" onclick="node(\''.$node."')\" class=\"node $nodeStatus\" title=\"".htmlentities($nodeInfo).'"></span>'."\n";
}

function do_systems($systems) {
    if ($systems) {
        $systems = explode("\n", trim($systems));
        foreach ($systems as $s) {
            do_node($s);
        }
    }
    else {
        echo "<span title='No managed systems'>&nbsp;&#x2205;</span>";
    }
}

function do_personalities($personalities, $archetype, $dnsDomains, $sectionTitle) {

    global $CONFIG;

    echo "<div class=\"cluster-container\"><h2 class=\"cluster\">$sectionTitle</h2>\n";
    foreach ($personalities as $personality) {
        $allSystems = Array();
        foreach ($dnsDomains as $dns_domain) {
            $queryReturn =file_get_contents($CONFIG['AQUILON']['URL'] . "find/host?personality=$personality&archetype=$archetype&dns_domain=$dns_domain");
            if (!empty ($queryReturn)) {
                $allSystems[] = $queryReturn;
            }
        }
        if (!empty($allSystems)) {
            echo "<div style=\"top: 0;\" class=\"cluster\" id=\"cl_{$archetype}_{$personality}\">\n";
            echo "<h5 class=\"cluster\" title=\"Archetype: $archetype\nPersonality: $personality\">$personality</h5>\n";
            foreach ($allSystems as $systems) {
                do_systems($systems);
            }
            echo "</div>\n";
        }
    }
    echo "</div>\n";
}

$personalities = Array();
$personality_info = file_get_contents($CONFIG['AQUILON']['URL'] . "personality?archetype=$archetype");
$personality_info = explode("\n", $personality_info);
foreach ($personality_info as $line) {
    $l = explode(' ', trim($line));
    if ($l[0] == 'Host' and $l[1] == 'Personality:') {
        $personalities[] = $l[2];
    }
}

//Actually Generating Page

//First Infrastructure
do_personalities($personalities, $archetype, Array($CONFIG['NUBES']['URL_RL'], $CONFIG['STFC']['URL']), 'Infrastructure');

//Then VMs
do_personalities($personalities, $archetype, Array($CONFIG['NUBES']['URL_STFC']), 'VMs');

?>
