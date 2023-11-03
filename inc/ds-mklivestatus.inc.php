<?php

require("inc/config-call.inc.php");

class liveStatus {

    static function get($table, $filter_col = "", $filter_val = "") {
        global $CONFIG;
        $LIVESTATUS_HOSTS = Array(
            $CONFIG['SERVER']['ICINGA1'],
            $CONFIG['SERVER']['ICINGA2'],
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
                if (strpos($host, 'icinga') !== -1) {
                    $get .= "\n";
                }
                $port = 9899;
                if (strpos($host, ':') !== -1) {
                    $host = explode(':', $host, 2);
                    $port = $host[1];
                    $host = $host[0];
                }
                $data = "";
                $cmd = "echo -e \"$get\" | nc -w 5 $host $port";
                exec($cmd, $data);

                if (count($data) > 0) {
                    # Get column name headers
                    $cols = explode("`", array_shift($data));
                    $cols = array_flip($cols);

                    if ((count($cols) > 0) and (count($data) > 0)) {
                        foreach ($data as $n => $d) {
                            $results[$host][$n] = Array();
                            $row = explode("`", $d);
                            foreach ($cols as $k => $v) {
                                $results[$host][$n][$k] = '';
                                if (isset($row[$v])) {
                                    $results[$host][$n][$k] = $row[$v];
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($results)) {
                return($results);
            }
            else {
                error("No data returned from", "mklivestatus");
                return(array());
            }
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
            $exploded = explode("`", $d);
            $hosts[$exploded[$cols["display_name"]]] = $exploded;
        }
        return(Array($cols, $hosts));
    }

}
