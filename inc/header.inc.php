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
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/layout.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
    <link rel="icon" href="images/mimic-icon.png" type="image/png" />
    <script type="text/javascript" src="js/monitor.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
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
        <span id="batch"        class="tab" title="Logical overview of worker nodes">Logical - Workers</span>
        <span id="overwatch"    class="tab" title="Logical overview of storage nodes">Logical - Storage</span>
        <span id="storage"      class="tab" title="Generational overview of storage nodes">Generational - Storage</span>
        <span id="generational" class="tab" title="Generational overview of all nodes">Generational - All</span>
        <span id="room"         class="tab" title="Physical overview of all nodes">Physical</span>
        <span id="aquilon-domains-sandboxes" class="tab" title="Nodes in aquilon domains and sandboxes">aq sandboxes</span>
        <span id="aquilon-personalities" class="tab" title="Nodes with aquilon personalities">aq personalities</span>
        <span id="cloud" class="tab" title="Overview of cloud nodes">Cloud</span>
        <span id="elasticsearch-routing" class="tab" title="Elasticsearch Routing Table">ES Routing</span>
        <span id="elasticsearch-hosts" class="tab" title="Elasticsearch Shards by Host">ES Hosts</span>
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
        <li><span class="node unknown note" title="Note (Has a note attached)">&nbsp;</span>Note</li>
        <li><span class="node downtime" title="Downtime (Is in scheduled downtime)">&nbsp;</span>Downtime</li>
        <li><span class="node unknown warning" title="Warning (Nagios Warning present)">&nbsp;</span>Warning</li>
        <li><span class="node unknown critical" title="Critical Alarm (Nagios Critical Alarm Present)">&nbsp;</span>Critical Alarm</li>
        <li><span class="node down" title="Down (Nagios cannot reach this host)">&nbsp;</span>Host Down</li>
      </ul>
      <ul class="key-view">
      </ul>
    </div>
<?php require('inc/functions.inc.php'); ?>
    <div id="farm"></div>
<?php /***** MAIN DOCUMENT STARTS HERE, NOTHING BELOW HERE PLEASE *****/ ?>
