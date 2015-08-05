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

$path = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
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

    private function getDBinfo($machineName)
    {
        $got = pg_query("select * from \"vCastor\" where \"machineName\" = '".mysql_real_escape_string($machineName)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function getOverwatchHistory($machineName)
    {
        $got = pg_query("select \"lastUpdateDate\",\"lastUpdatedBy\",\"currentStatus\",\"normalStatus\",\"currentTeam\",\"serviceType\",\"virtualOrganisation\",\"diskPool\",\"sizeTb\",\"isPuppetManaged\" as \"puppetManaged\",\"isQuattorManaged\" as \"quattorManaged\",\"miscComments\" from \"storageSystemArchives\" where \"machineName\" = '".mysql_real_escape_string($machineName)."' order by \"lastUpdateDate\" asc");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(Array());
        }
    }

    private function getMagdbInfo($machineName)
    {
        $got = pg_query("select \"systemId\", \"ipAddress\" from \"vNetwork\" where \"fqdn\" = '".mysql_real_escape_string($machineName)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function getHostnames($systemId)
    {
        $got = pg_query("select fqdn from \"vNetwork\" where \"systemId\" = '".mysql_real_escape_string($systemId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function getRack($systemId)
    {
        $got = pg_query("select \"roomName\",\"rackId\", \"systemRackPos\", \"categoryName\", \"vendorName\", \"serviceTag\", \"serviceTagURL\", \"lifestageName\" from \"vStatus\" where \"systemId\" = '".mysql_real_escape_string($systemId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_assoc($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function getRoomPdus($rackId)
    {
        $got = pg_query('SELECT "name", "roomBuilding", "roomName", "upsPowered" FROM "vRackRoomPdus" WHERE "rackId"=\''.mysql_real_escape_string($rackId)."'");
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function getInterfaces($systemId)
    {
        $got = pg_query('select "name", "macAddress", "ipAddresses", "description", "isBootInterface" from "vNetworkInterfaces" where "systemId" = \''.mysql_escape_string($systemId).'\'');
        if ($got and pg_num_rows($got)) {
            $row = pg_fetch_all($got);
            return($row);
        }
        else {
            return(null);
        }
    }

    private function render_history_rows($history)
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

    private function render_overwatch_info($overwatch_info)
    {
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

        $history = $this->getOverwatchHistory($NODE);
        if ($history) {
            echo "<h3><span class=\"rollup\" onclick=\"toggleRollup('#node-magdb-history');\" title=\"Rollup Section\">&#x25BE; Overwatch State History</span></h3>";
            echo "<div id=\"node-magdb-history\"";
            if (isset($_COOKIE["rollup_#node-magdb-history"]) and $_COOKIE["rollup_#node-magdb-history"] == "hidden") {
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
            $fr = Array();
            foreach (array_keys($history[0]) as $column) {
                $fr[$column] = $previous_row[$column];
            }
            $history[] = $fr;

            $previous_row = False;

            //Display history
            render_history_rows();
            echo "</table>";
            echo "</div>\n";
        }
        else {
            echo "<p class=\"info\">No Overwatch History Found</p>";
        }
    }

    private function render_pdu_list($room_pdus)
    {
        echo "<dl>\n";
        foreach ($room_pdus as $room_pdu) {
            $extra_info = '';
            if ($room_pdu['upsPowered'] == 't') {
                $extra_info = ' <b>← UPS Powered</b>';
            }
            printf("<dt>%s</dt><dd>%s %s%s</dd>\n", $room_pdu['name'], $room_pdu['roomBuilding'], $room_pdu['roomName'], $extra_info);
        }
        echo "</dl>\n";
    }

    function detail($NODE, $SHORT)
    {
        global $OVERWATCH_URL;
        global $HARDTRACK_URL;
        global $HELPDESK_URL;

        $renderer = new Horde_Text_Diff_Renderer_Inline();

        $magdb_info = $this->getMagdbInfo($NODE);
        if ($magdb_info !== null) {
            echo "<h3>System</h3>\n";

            echo "<dl>\n";
            foreach ($magdb_info as $col => $val) {
                if ($val !== null) {
                    if ($col == "systemId" && $val != "&nbsp;") {
                        $val = "&#x2116;&nbsp;<a href=\"$HARDTRACK_URL/?section=system&amp;a=$val\" title=\"View platform $val in HardTrack\">$val</a>\n";
                    }
                    echo "<dt>$col</dt><dd>$val</dd>\n";
                }
            }
            echo "</dl>\n";

            if ($magdb_info["systemId"] !== null) {
                echo "<dl>\n";

                $rack_info = $this->getRack($magdb_info["systemId"]);
                foreach ($rack_info as $col => $val) {
                    if ($col != "serviceTagURL" and $val !== null) {
                        if ($col == "serviceTag" && $val != "&nbsp;" && $rack_info["serviceTagURL"] !== null) {
                            $val = "<a href=\"".htmlspecialchars($rack_info["serviceTagURL"])."$val\" title=\"View details of service tag on Vendor's site\">$val</a>&#x219D;\n";
                        }
                        if ($col != 'lifestageName') {
                            # Hide lifestageName because we've been really bad at keeping it correct and it scares people.
                            echo "<dt>$col</dt><dd>$val</dd>\n";
                        }
                    }
                }
                echo "</dl>\n";

                echo "<h3>Rack Power</h3>\n";
                $room_pdus = $this->getRoomPdus($rack_info["rackId"]);
                if ($room_pdus) {
                    render_pdu_list($room_pdus);
                } else {
                    echo "<p class=\"warning\">No rack power information.</p>\n";
                }

                echo "<h3>Networking</h3>\n";
                echo "<object ";
                //Add work-around for webkit's broken SVG embedding.
                if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "webkit")) {
                    echo 'style="width: 100%;" ';
                }
                echo "type=\"image/svg+xml\" data=\"/components/magdb-draw-interfaces.php?system={$magdb_info["systemId"]}\"></object><!--alt=\"Graph of networking information\" title=\"Blue interfaces are bootable. Grey records are sourced from DNS.\" -->\n";
            } else {
                echo "<p class=\"warning\">Stub Record - No system associated with IP.</p>\n";
            }
        } else {
            echo "<p class=\"warning\">Host not in magDB.</p>\n";
        }

        $overwatch_info = $this->getDBinfo($SHORT);
        if ($overwatch_info !== null) {
            render_overwatch_info($overwatch_info);
        }
    }
}

return new pMagdb();
