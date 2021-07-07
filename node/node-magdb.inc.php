<?php

require_once('Horde/String.php');
require_once('Horde/Text/Diff.php');
require_once('Horde/Text/Diff/Engine/Native.php');
require_once('Horde/Text/Diff/Op/Base.php');
require_once('Horde/Text/Diff/Op/Add.php');
require_once('Horde/Text/Diff/Op/Copy.php');
require_once('Horde/Text/Diff/Op/Change.php');
require_once('Horde/Text/Diff/Renderer.php');
require_once('Horde/Text/Diff/Renderer/Inline.php');

include("inc/config-call.inc.php");
include("inc/db-magdb-open.inc.php");
include("inc/ouilookup.inc.php");

$OVERWATCH_URL = $CONFIG['URL']['OVERWATCH'];
$HARDTRACK_URL = $CONFIG['URL']['HARDTRACK'] . "hardtrack";
$HELPDESK_URL = $CONFIG['URL']['HELPDESK'];

class pMagdb
{
    function header($NODE, $SHORT)
    {
        return("magDB");
    }

    private function get_db_info($machineName)
    {
        global $SQL;

        $got = pg_query("select * from \"vCastor\" where \"machineName\" = '".$SQL->real_escape_string($machineName)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function get_overwatch_history($machineName)
    {
        global $SQL;

        $got = pg_query("select \"lastUpdateDate\",\"lastUpdatedBy\",\"currentStatus\",\"normalStatus\",\"currentTeam\",\"serviceType\",\"virtualOrganisation\",\"diskPool\",\"sizeTb\",\"isPuppetManaged\" as \"puppetManaged\",\"isQuattorManaged\" as \"quattorManaged\",\"miscComments\" from \"storageSystemArchives\" where \"machineName\" = '".$SQL->real_escape_string($machineName)."' order by \"lastUpdateDate\" asc");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(Array());
        }
    }

    private function get_magdb_info($machineName)
    {
        global $SQL;

        $got = pg_query("select \"systemId\", \"ipAddress\" from \"vNetwork\" where \"fqdn\" = '".$SQL->real_escape_string($machineName)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function get_hostnames($systemId)
    {
        global $SQL;

        $got = pg_query("select fqdn from \"vNetwork\" where \"systemId\" = '".$SQL->real_escape_string($systemId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function get_system($systemId)
    {
        global $SQL;

        $got = pg_query("select \"roomName\",\"rackId\", \"systemRackPos\", \"categoryName\", \"vendorName\", \"serviceTag\", \"serviceTagURL\", \"lifestageName\" from \"vStatus\" where \"systemId\" = '".$SQL->real_escape_string($systemId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function get_room_pdus($rackId)
    {
        global $SQL;

        $got = pg_query('SELECT "name", "roomBuilding", "roomName", "upsPowered" FROM "vRackRoomPdus" WHERE "rackId"=\''.$SQL->real_escape_string($rackId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function get_interfaces($systemId)
    {
        global $SQL;

        $got = pg_query('select "name", "macAddress", "ipAddresses", "description", "isBootInterface" from "vNetworkInterfaces" where "systemId" = \''.$SQL->escape_string($systemId).'\'');
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function render_history_rows($history, $previous_row, $renderer)
    {
        global $HELPDESK_URL;

        foreach ($history as $row) {
            // Bit of a hack to preserve blame for current record
            if ($row['lastUpdatedBy'] == 'Nobody') {
                $row['lastUpdatedBy'] = $previous_row['lastUpdatedBy'];
            }
            echo "<tr>";
            foreach ($row as $column => $cell) {
                if ($previous_row and $previous_row[$column] != $row[$column] and $column != "lastUpdateDate"){
                    $diff = new Horde_Text_Diff('auto', Array(Array((String) $previous_row[$column]), Array((String) $row[$column])));
                    $cell = $renderer->render($diff);
                }

                if ($column == "lastUpdateDate" and $cell != "Current") {
                    $cell = prettytime(time() - strtotime($cell));
                } elseif ($column == "miscComments" ) {
                    $cell = preg_replace("/#\s*([0-9][0-9]*)/", '<a href="'.$HELPDESK_URL.'/Ticket/Display.html?id=$1">#$1</a>', $cell);
                    $cell = preg_replace("/RT\s*([0-9][0-9]*)/", '<a href="'.$HELPDESK_URL.'/Ticket/Display.html?id=$1">RT$1</a>', $cell);
                } elseif (strpos($column, "Managed")) {
                    if ($cell == "t") {
                        $cell = "&#x2713;";
                    } else {
                        $cell = "&nbsp;";
                    }
                }

                if ($previous_row and $previous_row[$column] == $row[$column]) {
                    echo "<td class=\"unchanged\">$cell</td>";
                }
                else {
                    echo "<td class=\"changed\">$cell</td>";
                }
            }
            $previous_row = $row;
            echo "</tr>";
        }
    }

    private function render_overwatch_info($overwatch_info, $renderer)
    {
        global $HELPDESK_URL;
        global $OVERWATCH_URL;
        global $NODE;

        echo "<h3>Overwatch</h3>\n";
        echo "<dl>\n";
        foreach ($overwatch_info as $col => $val) {
            if ($val === null) {
                $val = "&nbsp;";
            }
            //Find RT ticket number and linkify them
            if ($col == "miscComments") {
                $val = preg_replace("/#\s*([0-9][0-9]*)/", '<a href="'.$HELPDESK_URL.'/Ticket/Display.html?id=$1">#$1</a>', $val);
                $val = preg_replace("/RT\s*([0-9][0-9]*)/", '<a href="'.$HELPDESK_URL.'/Ticket/Display.html?id=$1">RT$1</a>', $val);
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
            echo "<dt>$col</dt><dd>$val</dd>\n";
        }
        echo "</dl>\n";

        $history = $this->get_overwatch_history($NODE);
        if ($history) {
            echo "<h3><span class=\"rollup\" onclick=\"toggleRollup('#node-magdb-history');\" title=\"Rollup Section\">&#x25BE; Overwatch State History</span></h3>";
            echo "<div id=\"node-magdb-history\"";
            if (filter_input(INPUT_COOKIE, 'rollup_#node-magdb-history', FILTER_SANITIZE_STRING) == "hidden") {
                echo " style=\"display: none\"";
            }
            echo ">\n";
            echo "<table class=\"timeline\">";
            echo "<tr>";
            foreach (Array("When","Who","Current","Normal","Team","Service Type","VO","Pool","Size","P","Q","Comments") as $h) {
                echo "<th>$h</th>";
            }
            echo "</tr>";
            $previous_row = array_merge(Array("lastUpdatedBy" => "Nobody", "lastUpdateDate" => "Current"), $overwatch_info); # Previous row

            //Copy relevant parts of current state to history for comparison
            $history_data = Array();
            foreach (array_keys($history[0]) as $column) {
                $history_data[$column] = $previous_row[$column];
            }
            $history[] = $history_data;

            $previous_row = False;

            //Display history
            $this->render_history_rows($history, $previous_row, $renderer);
            echo "</table>";
            echo "</div>\n";
        }
        else {
            echo "<p class=\"info\">No Overwatch History Found</p>";
        }
    }

    private function render_pdu_list($room_pdus)
    {
        echo "<h3>Rack Power</h3>\n";
        if ($room_pdus) {
            echo "<dl>\n";
            foreach ($room_pdus as $room_pdu) {
                $extra_info = '';
                if ($room_pdu['upsPowered'] == 't') {
                    $extra_info = ' <b>← UPS Powered</b>';
                }
                printf("<dt>%s</dt><dd>%s %s%s</dd>\n", $room_pdu['name'], $room_pdu['roomBuilding'], $room_pdu['roomName'], $extra_info);
            }
            echo "</dl>\n";
        } else {
            echo "<p class=\"warning\">No rack power information.</p>\n";
        }
    }

    private function render_networking($magdb_info)
    {
        global $NODE;
        global $HELPDESK_URL;
        echo "<h3>Networking</h3>\n";
        echo "<object ";
        //Add work-around for webkit's broken SVG embedding.
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "webkit")) {
            echo 'style="width: 100%;" ';
        }
        echo "type=\"image/svg+xml\" data=\"/components/magdb-draw-interfaces.php?system={$magdb_info["systemId"]}\"></object><!--alt=\"Graph of networking information\" title=\"Blue interfaces are bootable. Grey records are sourced from DNS.\" -->\n";

        $node = htmlspecialchars($NODE);
        $systemId = $magdb_info["systemId"];

        echo "      <p>\n";
        echo "        New\n";
        echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Fabric&amp;Subject=$node\">Fabric</a>,\n";
        echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Fabric-Hardware&amp;Subject=$node\">Hardware</a>,\n";
        $body = urlencode(
            "As service owner of $node I am requesting that it be decommissioned as per the procedure documented at:\n" .
            "https://wiki.e-science.cclrc.ac.uk/web1/bin/view/EScienceInternal/GeneralProcedureForDecommissioningServers\n" .
            "\n" .
            "The hardware should be retired and disposed of.\n" .
            "The hardware should be put into holding for redeployment.\n" .
            "(Delete as appropriate)."
        );
        echo "        <a href=\"$HELPDESK_URL/Ticket/Create.html?Queue=Support&amp;Subject=$node%20%28System%20$systemId%29%20Server%20Decommissioning&amp;Content=$body\">Decommissioning</a>\n";
        echo "        Ticket\n";
        echo "      </p>\n";
    }

    function detail($NODE, $SHORT)
    {
        global $OVERWATCH_URL;
        global $HARDTRACK_URL;
        global $HELPDESK_URL;

        $renderer = new Horde_Text_Diff_Renderer_Inline();

        $magdb_info = $this->get_magdb_info($NODE);
        if ($magdb_info !== null) {
            echo "<h3>System</h3>\n";

            echo "<dl>\n";
            foreach ($magdb_info as $col => $val) {
                if ($val !== null) {
                    if ($col == "systemId" && $val != "&nbsp;") {
                        $val = "&#x2116;&nbsp;<a href=\"$HARDTRACK_URL/?section=system&amp;a=$val\" title=\"View platform $val in HardTrack\">$val</a>\n";
                    }
                   echo "<dt>$col</dt><dd class=\"magdb-$col\">$val</dd>\n";
                }
            }
            echo "</dl>\n";

            if ($magdb_info["systemId"] !== null) {
                echo "<dl>\n";

                $system_info = $this->get_system($magdb_info["systemId"]);
                foreach ($system_info as $col => $val) {
                    if ($col != "serviceTagURL" and $val !== null) {
                        if ($col == "serviceTag" && $val != "&nbsp;" && $system_info["serviceTagURL"] !== null) {
                            $val = "<a href=\"".htmlspecialchars($system_info["serviceTagURL"])."$val\" title=\"View details of service tag on Vendor's site\">$val</a>&#x219D;\n";
                        }
                        if ($col != 'lifestageName') {
                            # Hide lifestageName because we've been really bad at keeping it correct and it scares people.
                            echo "<dt>$col</dt><dd class=\"magdb-$col\">$val</dd>\n";
                        }
                    }
                }
                echo "</dl>\n";


                $room_pdus = $this->get_room_pdus($system_info["rackId"]);
                $this->render_pdu_list($room_pdus);

                $this->render_networking($magdb_info);
            } else {
                echo "<p class=\"warning\">Stub Record - No system associated with IP.</p>\n";
            }
        } else {
            echo "<p class=\"warning\">Host not in magDB.</p>\n";
        }

        $overwatch_info = $this->get_db_info($SHORT);
        if ($overwatch_info !== null) {
            $this->render_overwatch_info($overwatch_info, $renderer);
        }
    }
}

return new pMagdb();
