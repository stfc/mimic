<?php
global $CONFIG;
#Config
$PAKITI_SERVER = $CONFIG['PAKITI']['URL'];

class pPakiti2
{
    function header($NODE, $SHORT)
    {
        global $PAKITI_SERVER;
        return(Array("Pakiti 2", "<a class='tab-dark' href='https://$PAKITI_SERVER/pakiti/host.php?view=cve&h=$NODE'>Details</a>"));
    }

    function detail($NODE, $SHORT)
    {
        $result  = exec("/usr/bin/python node/node-pakiti2-json.py $NODE");
        $result  = str_replace("'", '"', $result);
        $jsonresult = json_decode($result, True);

        if (is_array($jsonresult)) {
            # Display host details
            echo "<dl>";
            if (array_key_exists("os", $jsonresult)) {
                echo "<dt>Operating System</dt><dd>{$jsonresult["os"]}</dd>";
            }
            if (array_key_exists("kernel", $jsonresult)) {
                echo "<dt>Running Kernel</dt><dd>{$jsonresult["kernel"]}</dd>";
            }
            if (array_key_exists("arch", $jsonresult)) {
                echo "<dt>Architecture</dt><dd>{$jsonresult["arch"]}</dd>";
            }
            if (array_key_exists("vulnerability", $jsonresult)) {
                echo "<dt>Vulnerability</dt><dd>{$jsonresult["vulnerability"]}</dd>";
            }
            echo "</dl>";

            # Loop over updates and display
            if (array_key_exists("updates", $jsonresult)) {
                $updates = $jsonresult["updates"];

                echo "<dl>";

                foreach ($updates as $k => $u) {
                    echo "<dt>$k</dt><dd>";

                    if (array_key_exists("security", $u)) {
                        echo "<span class='security' title='From {$u["current"]} to {$u["security"]}'>Security</span>";
                    }

                    if (array_key_exists("standard", $u)) {
                        echo "<span class='standard' title='From {$u["current"]} to {$u["standard"]}'>Standard</span>";
                    }

                    if (array_key_exists("cves", $u)) {
                        echo "CVEs: ";

                        foreach (array_count_values($u["cves"]) as $cs => $cn) {
                            echo "<span class='pakiti2cve ".strtolower($cs)."'>$cn $cs</span>";
                        }

                        "</span>";
                    }
                    echo "</dd>";
                }

                echo "</dl>";
                echo "<p><span class='time'>&#8634; ".prettytime(time() - $jsonresult["timestamp"])."</span></p>";
            }

            # Display any errors that were passed
            if (array_key_exists("errors", $jsonresult)) {
                $errors = $jsonresult["errors"];
                foreach ($errors as $e) {
                    echo "<p class='warning'>$e</p>";
                }
            }
        } else {
            echo "<p class='warning'>$result</p>";
        }
    }
}

return new pPakiti2();
