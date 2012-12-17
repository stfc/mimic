<?php

require_once("db-magdb-open.inc.php");
require_once("ouilookup.inc.php");

$OVERWATCH_URL = "https://overwatch.example.com";
$HARDTRACK_URL = "http://hardtrack.example.com";

class pMagdb
{
  function header($NODE, $SHORT)
  {
    return("magDB");
  }

  function getDBinfo($machineName)
  {
    $got = pg_query("select * from \"vCastor\" where \"machineName\" = '".mysql_escape_string($machineName)."'");
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_assoc($got);
      return($r);
    }
    else {
      return(null);
    }
  }

  function getOverwatchHistory($machineName)
  {
    $got = pg_query("select \"lastUpdatedBy\",\"lastUpdateDate\",\"currentStatus\",\"normalStatus\",\"currentTeam\",\"serviceType\",\"virtualOrganisation\",\"diskPool\",\"sizeTb\",\"isPuppetManaged\",\"isQuattorManaged\",\"miscComments\" from \"storageSystemArchives\" where \"machineName\" = '".mysql_escape_string($machineName)."' order by \"lastUpdateDate\" asc");
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_all($got);
      return($r);
    }
    else {
      return(Array());
    }
  }

  function getMagdbInfo($machineName)
  {
    $got = pg_query("select \"systemId\", \"ipAddress\" from \"vNetwork\" where \"fqdn\" = '".mysql_escape_string($machineName)."'");
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_assoc($got);
      return($r);
    }
    else {
      return(null);
    }
  }

  function getHostnames($systemId)
  {
    $got = pg_query("select fqdn from \"vNetwork\" where \"systemId\" = '".mysql_escape_string($systemId)."'");
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_all($got);
      return($r);
    }
    else {
      return(null);
    }
  } 

  function getRack($systemId)
  {
    $got = pg_query("select \"roomName\",\"rackId\", \"systemRackPos\", \"categoryName\", \"vendorName\", \"serviceTag\", \"serviceTagURL\", \"lifestageName\" from \"vStatus\" where \"systemId\" = '".mysql_escape_string($systemId)."'");
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_assoc($got);
      return($r);
    }
    else {
      return(null);
    }
  } 

  function getInterfaces($systemId)
  {
    $got = pg_query('select "name", "macAddress", "ipAddresses", "description", "isBootInterface" from "vNetworkInterfaces" where "systemId" = \''.mysql_escape_string($systemId).'\'');
    if ($got and pg_num_rows($got)) {
      $r = pg_fetch_all($got);
      return($r);
    }
    else {
      return(null);
    }
  }

  function detail($NODE, $SHORT)
  {
    global $OVERWATCH_URL;
    global $HARDTRACK_URL;

    $r = $this->getDBinfo($SHORT);
    if ($r !== null) {
      echo "      <dl>\n";
      foreach ($r as $col => $val) {
        if ($val === null) {
          $val = "&nbsp;";
        }
        //Find RT ticket number and linkify them
        if ($col == "miscComments") {
          $val = preg_replace("/#\s*([0-9][0-9]*)/", '<a href="https://helpdesk.example.com/Ticket/Display.html?id=$1">#$1</a>', $val);
          $val = "<span>$val</span>";
        } elseif ($col == "machineName") {
          $val = "<a href=\"$OVERWATCH_URL/index.php?update:$val\" title=\"Update Overwatch record\">$val</a>\n";
        } elseif ($col == "normalStatus") {
          $val = "<a href=\"$OVERWATCH_URL/index.php?view:status:$val\" title=\"Overwatch servers with status $val\">$val</a>\n";
        } elseif ($col == "diskPool") {
          $val = "<a href=\"$OVERWATCH_URL/index.php?view:diskpool:$val\" title=\"Overwatch servers in $val diskpool\">$val</a>\n";
        } elseif ($col == "castorInstance") {
          $val = "<a href=\"$OVERWATCH_URL/index.php?view:instance:$val\" title=\"Overwatch servers in $val instance\">$val</a>\n";
        }
        echo "        <dt>$col</dt><dd>$val</dd>\n";
      }
      echo "      </dl>\n";

      $val = "badgers";

      $history = $this->getOverwatchHistory($NODE);
      if ($history) {
        echo "<p><span class=\"rollup\" onclick=\"toggleRollup('#node-magdb-history');\" title=\"Rollup Section\">&#x25BE; Previous Overwatch States</span></p>";
        echo "<div id=\"node-magdb-history\"";
        if (isset($_COOKIE["rollup_#node-magdb-history"]) and $_COOKIE["rollup_#node-magdb-history"] == "hidden") {
          echo " style=\"display: none\"";
        }
		echo ">\n";
        echo "<table>";
        echo "<tr>";
        foreach (Array("Who","When","Current","Normal","Team","Service Type","VO","Pool","Size","P","Q","Comments") as $h) {
          echo "<th>$h</th>";
        }
        echo "</tr>";
        $pr = Array("Nobody", "Current").$r; # Previous row
        foreach ($history as $r) {
          echo "<tr>";
          foreach ($r as $k => $c) {
            if ($k == "lastUpdateDate") {
              $c = prettytime(time() - strtotime($c));
            } elseif ($k == "miscComments" ) {
              $c = preg_replace("/#\s*([0-9][0-9]*)/", '<a href="https://helpdesk.example.com/Ticket/Display.html?id=$1">#$1</a>', $c);
            } elseif (strpos($k, "Managed")) {
              if ($c == "t") {
                $c = "&#x2713;";
              } else {
                $c = "&nbsp;";
              }
            }
            if ($pr[$k] == $r[$k] or $k == "lastUpdateDate") {
              echo "<td class=\"unchanged\">$c</td>";
            }
            else {
              echo "<td>$c</td>";
            }
          }
          $pr = $r;
          echo "</tr>";
        }
        echo "</table>";
        echo "</div>\n";
      }
      else {
        echo "<p class=\"info\">No Previous Overwatch States Found</p>";
      }
    }

    $r2 = $this->getMagdbInfo($NODE);
    if ($r2 !== null) {

      echo "      <dl>\n";
      foreach ($r2 as $col => $val) {
        if ($val !== null) {
          if ($col == "systemId" && $val != "&nbsp;") {
            $val = "&#x2116;&nbsp;<a href=\"$HARDTRACK_URL/?section=system&amp;a=$val\" title=\"View platform $val in HardTrack\">$val</a>\n";
          }
          echo "        <dt>$col</dt><dd>$val</dd>\n";
        }
      }
      echo "      </dl>\n";

      if ($r2["systemId"] !== null) {
        echo "      <dl>\n";

        $r4 = $this->getRack($r2["systemId"]);
        foreach ($r4 as $col => $val) {
          if ($col != "serviceTagURL" and $val !== null) {
            if ($col == "serviceTag" && $val != "&nbsp;" && $r4["serviceTagURL"] !== null) {
              $val = "<a href=\"".htmlspecialchars($r4["serviceTagURL"])."$val\" title=\"View details of service tag on Vendor's site\">$val</a>&#x219D;\n";
            }
            echo "        <dt>$col</dt><dd>$val</dd>\n";
          }
        }
        echo "      </dl>\n";


        echo "<object ";
        //Add work-around for webkit's broken SVG embedding.
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "webkit")) {
          echo 'style="width: 100%;" ';
        }
        echo "type=\"image/svg+xml\" data=\"http://status.example.com/components/magdb-draw-interfaces.php?system={$r2["systemId"]}\"></object><!--alt=\"Graph of networking information\" title=\"Blue interfaces are bootable. Grey records are sourced from DNS.\" -->\n";
      } else {
        echo "<p class=\"warning\">Stub Record - No system associated with IP.</p>\n";
      }
    } else {
      echo "      <p class=\"warning\">Host not in magDB.</p>\n";
    }
  }
}

return new pMagdb();

?>
