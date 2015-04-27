<?php

class pGanglia
{

  function header()
  {
    return("Ganglia");
  }

  function detail($NODE, $SHORT)
  {
    $castorInstance = null;

    //Get numeric component of node name
    $node_num = (int) preg_replace("/[^0-9]/", "", $SHORT);

    $disks = array();
    $smart = array();
	$gmetrics = array();

    // Set the ganglia cluster
    if ( $castorInstance !== null ) {
      switch ($castorInstance) {
        case "Atlas":
	  $cluster = "Storage_CASTOR_ATLAS";
	  break;
	case "CMS":
	  $cluster = "Storage_CASTOR_CMS";
	  break;
	case "LHCb":
	  $cluster = "Storage_CASTOR_LHCb";
          break;
	case "Gen":
	  $cluster = "Storage_CASTOR_Gen";
      }
      $disks = array("exportstage_castor1", "exportstage_castor2", "exportstage_castor3");
    }
    else if (in_array($SHORT, array("lcg0614", "lcg0616", "lcg0621", "lcg0622", "lcg0623")) or ($node_num >= 729)) {
      $cluster = "Workers_SL5";
      $disks = array("pool", "home_pool");
      $smart = array("sda_Offline_Uncorrectable", "sda_Raw_Read_Error_Rate", "sda_Hardware_ECC_Recovered");
    }
    else if (($node_num >= 716) or ($node_num <= 728 )) {
      $cluster = "Services_HADOOP";
      $disks = array("pool");
	  $gmetrics = array("mapred.tasktracker.maps_running", "mapred.tasktracker.reduces_running", "dfs.datanode.writeBlockOp_avg_time");
    }
    else if ((strpos($NODE, 'lcgdb')  !== false) or (strpos($NODE, 'lcgsql')  !== false)) {
      $cluster = "Services_Database";
      $disks = array("opt");
    }
    else if (strpos($NODE, 'afs')  !== false) {
      $cluster = "Storage_AFS";
      $disks = array("vicepa", "vicepb", "vicepc", "vicepd", "vicepe");
    }
    else if (strpos($NODE, 'lcgcts')  !== false) {
      $cluster = "Services_CASTOR_Tape";
      $disks = array("var");
    }

    // Some useful variables
    $ganglia_url = 'http://ganglia.example.com';
    $graphs      = array("load_report", "cpu_report", "mem_report"); 
    $size        = 'medium';
    $timespan    = 'day';

    // Display the ganglia graph and link it to the ganglia host page if cluster has been determined
    if ( isset( $cluster ) ) {
      echo "      <p>\n";
      echo "        <a href=\"$ganglia_url/ganglia/?c=$cluster&amp;h=$NODE&amp;r=$timespan&amp;s=descending\" class=\"image\">\n";
      foreach( $graphs as $graph ) {
        echo "          <img src=\"$ganglia_url/ganglia/graph.new.php?g=$graph&amp;z=$size&amp;c=$cluster&amp;h=$NODE&amp;r=$timespan\" alt=\"$NODE $graph\" />\n";
      }
      foreach( $gmetrics as $g ) {
        echo "          <img src=\"$ganglia_url/ganglia/graph.new.php?c=$cluster&amp;h=$NODE&amp;m=$g&amp;r=$timespan&amp;z=$size\" alt=\"$NODE $g\" />\n";
      }
      foreach( $disks as $disk ) {
        echo "          <img src=\"$ganglia_url/cgi-bin/ganglia-df/df-graph.pl?r=$timespan&amp;s=$size&amp;d=$disk&amp;m=$NODE&amp;cluster=$cluster\" alt=\"$NODE Disk Usage for $disk\" />\n";
      }
      foreach( $smart as $s ) {
        echo "          <img src=\"$ganglia_url/ganglia/graph.new.php?c=$cluster&amp;h=$NODE&amp;m=smart_$s&amp;r=$timespan&amp;z=$size&amp;color=cc0000\" alt=\"$NODE SMART attribute $s\" />\n";
      }
      echo "        </a>\n";
      echo "      </p>\n";
    }
    else {
      echo "      <p class=\"info\">Ganglia mimic integration not implemented for this host.</p>\n"; //Fail :(
    }
  }
}

return new pGanglia();
