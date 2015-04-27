<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT'); 
?>
<!DOCTYPE html>
<?php

# Some variables to get us started...
$NODES = array (); # For names from csf_monitor
$SHORT = array (); # For names from nagios

# Page title gimmick
$fortune = array ('Mimi Is Monitoring Infinitely',
                  'Mimi Is Monitoring Information',
                  'Mimi Is Mostly Incomplete',
                  'Mimi Is Mainly Incredible',
                  'Mimi Is a Marvelous Informer',
                  'Mystical Information Montoring Interface',
                  'Mimi Is Martin\'s Informant');
$title = $fortune[rand(0, count($fortune) - 1)];

//Which page are we viewing?
if (isset($_REQUEST['page']))
  $page = mysql_escape_string($_REQUEST['page']);
else
  $page = 1;

?>
<html>
  <head>
    <title>Tier1 Mimic</title>
    <!-- Tenuous acronym of the moment: <?php echo $title; ?> -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" media="screen" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <script type="text/javascript" src="components/monitor.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
  </head>
  <body>
<?php
  if (file_exists("components/page$page.inc")) {
    echo "<!-- Start of page $page specific content -->\n";
    include("components/page$page.inc");
    echo "<!-- End of page $page specific content -->\n";
  }
?>
    <div id="menu">
      <p>
        <span id="batch"     class="tab" title="Logical overview of worker nodes">Logical - Workers</span>
        <span id="overwatch" class="tab" title="Logical overview of storage nodes">Logical - Storage</span>
        <span id="storage"   class="tab" title="Generational overview of storage nodes">Generational - Storage</span>
        <span id="room"      class="tab" title="Physical overview of all nodes">Physical</span>
        &nbsp;
        <input type="text" id="inLocate" placeholder="Search by name" title="Names to search for (space or comma seperated)"/>
        &nbsp;
        <span id="btnkey" class="btn">Key</span>
        <script>
          $("#inLocate").keyup(function(e) {
            locateNode(this.value);
          });
          $(".tab").click(function(e) {
            $(".tab").removeClass("active");
            $(this).addClass("active");
            view = this.id;
            $("#farm").html(msg_loading);
            update();
          });
          $("#btnkey").click(function(e) {
            $(this).toggleClass("active");
            $("#key").slideToggle();
          });
        </script>
      </p>
    </div>
    <div id="key">
      <ul class="key">
        <li><span class="node unknown" title="Unknown (Not known to the batch system)">&nbsp;</span>Non-Batch Host</li>
        <li><span class="node free" title="Free (In the batch system, not running any jobs)">&nbsp;</span>Free</li>
        <li><span class="node inuse" title="In Use (In the batch system, running jobs but not full)">&nbsp;</span>In Use</li>
        <li><span class="node full" title="Full (In the batch system, running jobs and full)">&nbsp;</span>Full</li>
        <li><span class="node offline" title="Offline (In the batch system, not open to new jobs)">&nbsp;</span>Offline</li>
        <li><span class="node batchdown" title="Down In the batch system (Cannot be reached by scheduler)">&nbsp;</span>Client Down</li>
        <li><span class="node unknown note" title="Note (Has a note attached)">&nbsp;</span>Note</li>
        <li><span class="node downtime" title="Downtime (Is in scheduled downtime)">&nbsp;</span>Downtime</li>
        <li><span class="node unknown warning" title="Warning (Nagios Warning present)">&nbsp;</span>Warning</li>
        <li><span class="node unknown critical" title="Critical Alarm (Nagios Critical Alarm Present)">&nbsp;</span>Critical Alarm</li>
        <li><span class="node down" title="Down (Nagios cannot reach this host)">&nbsp;</span>Host Down</li>
        <li><span class="node castorProduction" title="Node is in Production">&nbsp;</span>Production</li>
        <li><span class="node castorHolding" title="Node is in holding state">&nbsp;</span>Holding</li>
        <li><span class="node castorDeployment" title="Node is being deployed">&nbsp;</span>Deployment</li>
        <li><span class="node castorReadOnly" title="Node is read only">&nbsp;</span>ReadOnly</li>
        <li><span class="node castorDraining" title="Node is draining">&nbsp;</span>Draining</li>
        <li><span class="node castorIntervention" title="Node is in intervention ">&nbsp;</span>Intervention</li>
        <li><span class="node castorDecomissioned" title="Node has been decomissioned">&nbsp;</span>Decomissioned</li>
      </ul>
    </div>
<?php require('functions.inc.php'); ?>
    <div id="farm">
<?php /***** MAIN DOCUMENT STARTS HERE, NOTHING BELOW HERE PLEASE *****/ ?>
