<?php

#Config
$NAGIOS_URL = $CONFIG['URL']['NAGIOS'];
$HELPDESK_URL = $CONFIG['URL']['HELPDESK'];

#Workhorse
class pNagios
{
    function header($node, $short)
    {
        global $DB_NAGIOS_NAME;
        global $NAGIOS_SERVER;
        global $NAGIOS_URL;
        global $NAGIOS_V;

        # Nagios Livestatus
        require_once("inc/ds-nagioslivestatus.inc.php");

        $n_state = nagiosLiveStatus::get("hosts", "host_name", $short);
        $objectname = $short;

        # Try short hostname first, if it fails try fqdn
        if (sizeof($n_state) == 0) {
            $n_state = nagiosLiveStatus::get("hosts", "host_name", $node);
            $objectname = $node;
        }

        $items = Array("Nagios");

        foreach ($n_state as $server => $state) {
            // $items[] = "<span class=\"text-muted\">$server: </span>"; does this need to be shown?
            $items[] = "<a class=\"tab-dark\" href=\"".sprintf($NAGIOS_URL, $server)."/cgi-bin/status.cgi?host=".htmlspecialchars($objectname)."\">Status Details</a>";
            $items[] = "<a class=\"tab-dark\" href=\"".sprintf($NAGIOS_URL, $server)."/cgi-bin/cmd.cgi?cmd_typ=55&amp;host=".htmlspecialchars($objectname)."\">Schedule Downtime</a>";
        }

        return($items);
    }

    function detail($node, $short)
    {
        global $DB_NAGIOS_NAME;
        global $NAGIOS_SERVER;
        global $NAGIOS_URL;
        global $NAGIOS_V;
        global $HELPDESK_URL;

        # Nagios Livestatus
        require_once("inc/ds-nagioslivestatus.inc.php");

        $n_state = nagiosLiveStatus::get("hosts", "host_name", $short);
        $objectname = $short;

        # Try short hostname first, if it fails try fqdn
        if (sizeof($n_state) == 0) {
            $n_state = nagiosLiveStatus::get("hosts", "host_name", $node);
            $objectname = $node;
        }
        $n_services = nagiosLiveStatus::get("services", "host_name", $objectname);
        $n_downtimes = nagiosLiveStatus::get("downtimes", "host_name", $objectname);

        // Service Alarms
        if (is_array($n_services) && sizeof($n_services) > 0) {
            $count = 0;
            echo "      <table class=\"nagios\">\n";

            foreach ($n_services as $server => $services) {
                echo "<!-- $server -->\n";
                foreach ($services as $s) {
                    if ($s["state"] > 0) {
                        $count += 1;
                        switch ($s["state"]) {
                            case  1: $s_alarm = "\"warn\">WARNING"; break;
                            case  2: $s_alarm = "\"crit\">CRITICAL"; break;
                            case  3: $s_alarm = "\"unkn\">UNKNOWN"; break;
                            case  4: $s_alarm = "\"unkn\">CLEARED"; break;
                            default: $s_alarm = "\"unkn\">? WTF ?";
                        }
                        echo "<tr>";
                        echo "<td class=".$s_alarm."</td>";
                        $name = $s["display_name"];
                        echo "<td";
                        if ($s["acknowledged"]) {
                            echo " class=\"ack\"";
                        }
                        $text=$s["plugin_output"];
                        echo "><a href=\"".sprintf($NAGIOS_URL, $server)."/cgi-bin/extinfo.cgi?type=2&amp;host=$objectname&amp;service=".rawurlencode($name)."\">$name</a></td>";
                        echo "<td>$text";
                        //Create new ticket link-btn
                        echo "&nbsp;<a title=\"Create new ticket\" href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Fabric&amp;Subject=";
                        echo rawurlencode($node." - ".$name)."&amp;Content=".rawurlencode("Nagios check ".$name." returned ".$text)."\"><img src=\"images/icons/document-new.png\" alt=\"Create new ticket\" /></a>\n";
                        echo "</td>";

                        echo "<td><span style=\"white-space: nowrap;\" class=\"time\">&#8634; ".prettytime(time() - $s["last_state_change"])."</span></td>";
                        echo "</tr>\n";
                    }
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
        if (is_array($n_state)) {
            foreach ($n_state as $server => $state) {
                echo "<!-- $server -->\n";
                foreach ($state as $s) {
                    echo "      <div id=\"nagios-state\">\n";

                    if ($s["state"] != 0) {
                        echo "      <p class=\"warning\">System down/unreachable</p>\n";
                    }

                    echo "      </div>\n";
                }
            }
        }


        //Get details of downtimes
        if (is_array($n_downtimes) && sizeof($n_downtimes) > 0) {
            echo "      <div id=\"nagios-downtimes\">\n";
            echo "         <h2>Scheduled Downtime</h2>\n";
            echo "         <table class=\"simple\">\n";
            echo "          <tr><th>Start</th><th>End</th><th>Progress</th><th>User</th><th>Reason</th><th>&nbsp;</th></tr>\n";
            foreach ($n_downtimes as $server => $downtimes) {
                echo "<!-- $server -->\n";
                foreach ($downtimes as $d) {
                    $dt_start  = prettytime(time() - (int)$d["start_time"]);
                    $dt_end    = prettytime(time() - (int)$d["end_time"]);
                    $dt_user   = ucwords(preg_replace("!.*=!", "", $d["author"]));
                    $dt_reason = $d["comment"];
                    $dt_id     = $d["id"];

                    $dt_duration = (int)$d["end_time"] - (int)$d["start_time"];
                    $dt_progress = (( time() - (int)$d["start_time"]) / $dt_duration) * 100;

                    echo '<tr>';
                    echo '<td class="fixed">'.$dt_start.'</td>';
                    echo '<td class="fixed">'.$dt_end.'</td>';
                    echo '<td>'.sprintf("%2.0f%%", $dt_progress).'</td>';
                    echo '<td>'.$dt_user.'</td>';
                    echo '<td class="fixed">'.$dt_reason."</td>";
                    echo '<td>';
                    echo '<a class="delete" href="'.sprintf($NAGIOS_URL, $server).'/cgi-bin/cmd.cgi?cmd_typ=78&amp;down_id='.$dt_id.'" />';
                    echo '</td>';
                    echo "</tr>\n";
                }
            }
            echo "         </table>\n";
        }
    }
}

return new pNagios();
