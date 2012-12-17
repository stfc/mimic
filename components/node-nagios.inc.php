<?php

require_once("db-open.inc.php");

#Config
$NAGIOS_SERVER = "nagios.example.com";
$NAGIOS_URL = "https://$NAGIOS_SERVER/thruk";

#Workhorse
class pNagios
{
  function header($node, $short)
  {
    global $DB_NAGIOS_NAME;
    global $NAGIOS_SERVER;
    global $NAGIOS_URL;
    global $NAGIOS_V;

    return(Array("Nagios", "<a class=\"button\" href=\"$NAGIOS_URL/cgi-bin/status.cgi?host=".htmlspecialchars($short)."\">Status Details</a>", "<a class=\"button\" href=\"$NAGIOS_URL/cgi-bin/cmd.cgi?cmd_typ=55&amp;host=".htmlspecialchars($short)."\">Schedule Downtime</a>"));
  }

  function detail($node, $short)
  {
    global $DB_NAGIOS_NAME;
    global $NAGIOS_SERVER;
    global $NAGIOS_URL;
    global $NAGIOS_V;

    # Nagios Livestatus
    require("components/ds-nagioslivestatus.inc.php");

    $n_services = nagiosLiveStatus::get("services", "host_name", $short);
    $n_services_cols = $n_services[0];
    $n_services = $n_services[1];
    
    $n_downtimes = nagiosLiveStatus::get("downtimes", "host_name", $short);
    $n_downtimes_cols = $n_downtimes[0];
    $n_downtimes = $n_downtimes[1];

    $n_state = nagiosLiveStatus::get("hosts", "host_name", $short);
    $n_state_cols = $n_state[0];
    $n_state = $n_state[1];
    
    // Service Alarms
    if (is_array($n_services_cols) && is_array($n_services) && sizeof($n_services) > 0) {
        $c = $n_services_cols;
        $count = 0;
        echo "      <table class=\"nagios\">\n";
        
        foreach ($n_services as $s) {
            $s = explode("`", $s);
            if ($s[$c["state"]] > 0) {
                $count += 1;
                switch ($s[$c["state"]]) {
                    case  1: $s_alarm = "\"warn\">WARNING"; break;
                    case  2: $s_alarm = "\"crit\">CRITICAL"; break;
                    case  3: $s_alarm = "\"unkn\">UNKNOWN"; break;
                    case  4: $s_alarm = "\"unkn\">CLEARED"; break;
                    default: $s_alarm = "\"unkn\">? WTF ?";
                }
                echo "<tr>";
                echo "<td class=".$s_alarm."</td>";
                $name = $s[$c["display_name"]];
                echo "<td";
                if ($s[$c["acknowledged"]]) {
                    echo " class=\"ack\"";
                }
                $text=$s[$c["plugin_output"]];
                echo "><a href=\"$NAGIOS_URL/cgi-bin/extinfo.cgi?type=2&amp;host=$short&amp;service=".rawurlencode($name)."\">$name</a></td>";
                echo "<td>$text";
                //Create new ticket link-button
                echo "&nbsp;<a title=\"Create new ticket\" href=\"https://helpdesk.example.com/Ticket/Create.html?Queue=Fabric&amp;Subject=";
                echo rawurlencode($node." - ".$name)."&amp;Content=".rawurlencode("Nagios check ".$name." returned ".$text)."\"><img src=\"images/icons/actions/document-new.png\" alt=\"Create new ticket\" /></a>\n";
                echo "</td>";

                echo "<td><span style=\"white-space: nowrap;\" class=\"time\">&#8634; ".prettytime(time() - $s[$c["last_state_change"]])."</span></td>";
                echo "</tr>\n";
            }
        }
        echo "      </table>\n";

        if ($count == 0) {
            echo "      <p class=\"info\">No service alarms.</p>\n";
        }
    }
    else {
      echo "      <p class=\"warning\">No info found for host.</p>\n";
    }

    //Try to get state from Nagios database
    if (is_array($n_state) && sizeof($n_state) == 1) {
        $s = explode("`", $n_state[0]);
        $c = $n_state_cols;
        echo "      <div id=\"nagios-state\">\n";

        if ($s[$c["state"]] != 0) {
            echo "      <p class=\"info\">In scheduled downtime</p>\n";
        }

        if ($s[$c["state"]] == 1) {
           echo "      <p class=\"warning\">System down</p>\n";
        }

        echo "      </div>\n";
    }


    //Get details of downtimes
    if (is_array($n_downtimes) && sizeof($n_downtimes) > 0) {
      $c = $n_downtimes_cols;
      echo "      <div id=\"nagios-downtimes\">\n";
      echo "         <h2>Scheduled Downtime</h2>\n";
      echo "         <table class=\"simple\">\n";
      echo "          <tr><th>Start</th><th>End</th><th>Progress</th><th>User</th><th>Reason</th><th>&nbsp;</th></tr>\n";
      foreach ($n_downtimes as $d) {
        $d = explode("`", $d);
        $dt_start  = prettytime(time() - (int)$d[$c["start_time"]]);
        $dt_end    = prettytime(time() - (int)$d[$c["end_time"]]);
        $dt_user   = ucwords(preg_replace("!.*=!", "", $d[$c["author"]]));
        $dt_reason = $d[$c["comment"]];
        $dt_id     = $d[$c["id"]];

		$dt_duration = (int)$d[$c["end_time"]] - (int)$d[$c["start_time"]];
		$dt_progress = (( time() - (int)$d[$c["start_time"]]) / $dt_duration) * 100;

        echo '          <tr><td class="fixed">'.$dt_start.'</td><td class="fixed">'.$dt_end.'</td><td>'.sprintf("%2.0f%%", $dt_progress).'</td><td>'.$dt_user.'</td><td class="fixed">'.$dt_reason."</td><td><a class=\"delete\" href=\"$NAGIOS_URL/cgi-bin/cmd.cgi?cmd_typ=78&amp;down_id=".$dt_id."\" /></td></tr>\n";
      }
      echo "         </table>\n";
    }
  }
}

return new pNagios();
?>
