<?php

require("inc/config-call.inc.php");

class nagiosLiveStatus {

    static function get($table, $filter_col = "", $filter_val = "") {
        global $CONFIG;
        $nagios1 = $CONFIG['URL']['NAGIOS1'];
        $nagger = $CONFIG['URL']['NAGGER'];
        $LIVESTATUS_HOSTS = Array(
            "$nagios1",
            "$nagger",
        );
        $LIVESTATUS_TABLES = Array(
            "hosts",
            "services",
            "hostgroups",
            "servicegroups",
            "contactgroups",
            "servicesbygroup",
            "servicesbyhostgroup",
            "hostsbygroup",
            "contacts",
            "commands",
            "timeperiods",
            "downtimes",
            "comments",
            "log",
            "status",
            "columns",
        );

        if (in_array($table, $LIVESTATUS_TABLES)) {
            $data = Null;

            $get = "GET $table";
			$get .= "\\nSeparators: 10 96 44 124";

            if (strlen($filter_col) > 0 && strlen($filter_val) > 0) {
                $filter_col = preg_replace("/[^a-zA-Z0-9_-]/", "", $filter_col);
                $filter_val = preg_replace("/[^a-zA-Z0-9_\.-]/", "", $filter_val);
                $get .= "\\nFilter: $filter_col = $filter_val";
            }

            $results = Array();

            foreach ($LIVESTATUS_HOSTS as $host) {
                $data = "";
                $cmd = "echo -e \"$get\" | nc $host 9899";
                exec($cmd, $data);

                if (count($data) > 0) {
                    # Get column name headers
                    $cols = explode("`", array_shift($data));
                    $cols = array_flip($cols);

                    if ((count($cols) > 0) and (count($data) > 0)) {
                        foreach ($data as $n => $d) {
                            $row = explode("`", $d);
                            foreach ($cols as $k => $v) {
                                $results[$host][$n][$k] = $row[$v];
                            }
                        }
                    }
                }
            }

            return($results);
        }
        else {
            return(Null);
        }
    }

    static function get_hosts() {
        $raw = self::get("hosts");
        $result = Array();
        foreach ($raw as $server => $data) {
            foreach ($data as $row) {
                $result[$server][$row['name']] = $row;
            }
        }
        return $result;
    }

    static function get_host($hostname) {
        $raw = self::get("hosts", "display_name", $hostname);
        $cols = $raw[0];
        $data = $raw[1];
        unset($raw);

        # Split rows into columns and munge into new array
        $hosts = Array();
        foreach($data as $d) {
            $e = explode("`", $d);
            $hosts[$e[$cols["display_name"]]] = $e;
        }
        return(Array($cols, $hosts));
    }

}
