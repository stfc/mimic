<?php
//Important includes
require("header.php");

//Go find all our nodes
$instance = '';
$diskpool = '';
$num = 0;
$allnodes = pg_query(
  "select \"machineName\" as \"short\", \"fqdn\", \"castorInstance\", \"diskPool\", \"dxtx\", \"currentStatus\" "
  ."from \"vCastorFQDN\" "
  ."where \"normalStatus\" not in ('Retired', 'Decomissioned') "
  ."order by \"castorInstance\", \"diskPool\", \"machineName\";"
  );
if ($allnodes and pg_num_rows($allnodes)) {
  while ($r = pg_fetch_assoc($allnodes)) {
    /**
     * Start of main loop...
     */

    //We're looking at this node
    $node  = $r['fqdn'];
    $short = $r['short'];
    $currStat = $r['currentStatus'];

    //In this diskpool
    if ($r['diskPool'] != $diskpool) {
      if ($diskpool != '') {
        echo "        </div>\n";
      }
      $diskpool = $r['diskPool'];
      $dxtx     = $r['dxtx'];
      $s_diskpool = str_replace("/", "", $diskpool) . " ($dxtx)";

      //In this instance
      if ($r['castorInstance'] != $instance) {
        if ($instance != '') {
          echo "      </div>\n";
        }
        $instance = $r['castorInstance'];
        $s_instance = str_replace("/", "", $instance);

        echo "      <div class=\"instance\" id=\"ins_$s_instance\">\n";
        echo "        <p class=\"instance\">$s_instance</p>\n";
      }

      echo "        <div class=\"diskpool\" id=\"dp_$s_diskpool\">\n";
      echo "          <p class=\"diskpool\">$s_diskpool</p>\n";
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
    $nodeInfo   = "$node";

    $nodeNote = $nodecsf[0];

    $ntup = nagios_state($short, $node, $nodeStatus);
    if ($ntup) {
      $nodeStatus = $ntup[0];
      $nodeInfo .= '<br>'.$ntup[1];
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

    // Apply castor status
    $nodeStatus .= " castor" . $currStat;

    # And show it
    echo '          <span id="n_'.$short.'" onclick="node(\''.$node."')\" class=\"node $nodeStatus\" title=\"".htmlentities($nodeInfo).'"></span>'."\n";
  }
  echo "        </div>\n";
  echo "      </div>\n";
}

?>
