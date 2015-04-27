<style type="text/css">
  @import url("css/style-castor.css");
</style>
<?php

# MySQL Data sources
require("components/db-open.inc.php");

# Postgres Data Sources
require("components/db-magdb-open.inc.php");

# Nagios Livestatus
require("components/ds-nagioslivestatus.inc.php");

# Nagios library
require("components/main-nagios.inc.php");

//Go find all our nodes
$instance = '';
$hardwareGroup = '';
$num = 0;
$allnodes = pg_query(
  "select \"fqdn\" as \"name\", \"machineName\" as \"short\", \"currentStatus\", \"hardwareGroup\", \"castorInstance\" "
 ."from \"vCastorFQDN\" "
 ."order by \"hardwareGroup\", \"machineName\";"
);
if ($allnodes and pg_num_rows($allnodes)) {
  while ($r = pg_fetch_row($allnodes)) {
    /**
     * Start of main loop...
     */

    //We're looking at this node
    $node  = $r[0];
    $short = $r[1];

    $instance = $r[2];
    $currStat = $r[2];

	$preprod = ($r[4] == "Preprod");
    
    //In this diskpool
    if ($r[3] != $hardwareGroup) {
      if ($hardwareGroup != '') {
        echo "          </div>\n";
        echo "        </div>\n";
      }

      $hardwareGroup = $r[3];

      $s_hardwareGroup = str_replace("/", "", $hardwareGroup);

      echo "        <div class=\"instance\" id=\"dp_$s_hardwareGroup\">\n";
      echo "          <p class=\"instance\">$s_hardwareGroup</p>\n";
      echo "          <div class=\"diskpool\">\n";
    }

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
    $nodeInfo   = "";
  
    $nodeNote = $nodecsf[0];

    $nodeInfo = "<h4>$node</h4>";
    $nodeInfo .= "<p><b>MagDB:</b> $currStat</p>";

    $ntup = nagios_state($short, $node, $nodeStatus);
    if ($ntup[1]) {
        $nodeStatus = $ntup[0];
        $nodeInfo .= "<p><b>Nagios:</b> {$ntup[1]}</p>";
    }
    unset($ntup);

    //Process notes
    if (strlen($nodeNote) > 0) {
      //Tack note onto end of info string
      $nodeInfo .= ' - '.$nodeNote;
      
      //We want to be case insensitive!
      $s = strtolower($nodeNote);
      
      $nodeStatus .= ' note';
    }

    // Apply castor status
    $nodeStatus .= " castor" . $currStat;

	if ($preprod) {
      $nodeStatus .= " castorPreprod";
	}
  
    # And show it
    echo '          <span id="n_'.$short.'" onclick="node(\''.$node."')\" class=\"node $nodeStatus\" title=\"".htmlentities($nodeInfo).'"></span>'."\n";
  }
  echo "        </div>\n";
  echo "      </div>\n";
}

?>
