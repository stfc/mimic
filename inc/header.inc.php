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

// Which page are we viewing?
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
  <!-- Needs updating -->
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
  <link rel="icon" href="images/mimic-icon.png" type="image/png" />
  <script type="text/javascript" src="js/monitor.js"></script>
  <script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
  <!-- Needs updating -->
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>
  <div id="menu">
    <!-- IDs need updating -->
    <span id="batch" class="tab" title="Logical overview of worker nodes">Logical - Workers</span>
    <span id="logical-storage" class="tab" title="Logical overview of storage nodes">Logical - Storage</span>
    <span id="generational-storage" class="tab" title="Generational overview of storage nodes">Generational - Storage</span>
    <span id="generational-all" class="tab" title="Generational overview of all nodes">Generational - All</span>
    <span id="physical" class="tab" title="Physical overview of all nodes">Physical</span>
    <span id="aquilon-sandboxes" class="tab" title="Nodes in aquilon domains and sandboxes">aq sandboxes</span>
    <span id="aquilon-personalities" class="tab" title="Nodes with aquilon personalities">aq personalities</span>
    <span id="cloud" class="tab" title="Overview of cloud nodes">Cloud</span>
    <span id="elasticsearch-routing" class="tab" title="Elasticsearch Routing Table">ES Routing</span>
    <span id="elasticsearch-hosts" class="tab" title="Elasticsearch Shards by Host">ES Hosts</span>
    &nbsp;
    <input type="text" id="inLocate" placeholder="Search by name" title="Names to search for (space or comma seperated)"/>
    &nbsp;
    <span id="btnkey" class="btn">Key</span>
    <script>
      // Allows user to search
      $("#inLocate").keyup(function(e) {
        locateNode(this.value);
      });
      // Makes menu functional
      $(".tab").click(function(e) {
        $(".tab").removeClass("active");
        $(this).addClass("active");
        view = this.id;
        $("#farm").html(msg_loading);
        update();
      });
      // Shows and hides key 
      $("#btnkey").click(function(e) {
        $(this).toggleClass("active");
        $("#key").slideToggle();
      });
    </script>
  </div>
  <!-- Needs to change per view -->
  <div id="key">
    <ul class="key">
      
      <li><span class="node unknown note">&nbsp;</span>Note</li>
      <li><span class="node downtime">&nbsp;</span>Downtime</li>
      <li><span class="node unknown warning">&nbsp;</span>Warning</li>
      <li><span class="node unknown critical">&nbsp;</span>Critical Alarm</li>
      <li><span class="node down">&nbsp;</span>Host Down</li>

      <li><span class="node unknown">&nbsp;</span>unknown</li>
      <li><span class="node full">&nbsp;</span>Full</li>
      <li><span class="node inuse">&nbsp;</span>Inuse</li>
      <li><span class="node offline">&nbsp;</span>Offline</li>
      <li><span class="node free">&nbsp;</span>Free</li>

      <li><span class="node castorReadOnly">&nbsp;</span>castorReadOnly</li>
      <li><span class="node castorReady">&nbsp;</span>castorReady</li>
      <li><span class="node castorHolding">&nbsp;</span>castorHolding</li>

      <li><span class="node cloud-bad">&nbsp;</span>cloud-bad</li>
      <li><span class="node free replica">&nbsp;</span>replica</li>
    </ul>
    <ul class="key-view">
    </ul>
  </div>
  <?php require 'functions.inc.php'; ?>
  <div id="farm"></div>
  <?php /***** MAIN DOCUMENT STARTS HERE, NOTHING BELOW HERE PLEASE *****/ ?>
