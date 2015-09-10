<?php
global $CONFIG;
#Config
$PAKITI_SERVER = $CONFIG['PAKITI']['URL'];

class pPakiti2
{
  function header($NODE, $SHORT)
  {
    global $PAKITI_SERVER;
    return(Array("Pakiti 2", "<a class=\"tab-dark\" href=\"https://$PAKITI_SERVER/pakiti/host.php?h=$NODE\">Details</a>"));
  }

  function detail($NODE, $SHORT)
  {
    $result  = exec("/usr/bin/python node/node-pakiti2-json.py $NODE");
    $result  = str_replace("'", '"', $result);
    $result = json_decode($result, True);

    if (is_array($result)) {
      # Display host details
      echo "      <dl>\n";
      if (array_key_exists("os", $result)) {
        echo "        <dt>Operating System</dt><dd>{$result["os"]}</dd>\n";
      }
      if (array_key_exists("kernel", $result)) {
        echo "        <dt>Running Kernel</dt><dd>{$result["kernel"]}</dd>\n";
      }
      if (array_key_exists("arch", $result)) {
        echo "        <dt>Architecture</dt><dd>{$result["arch"]}</dd>\n";
      }
      if (array_key_exists("vulnerability", $result)) {
        echo "        <dt>Vulnerability</dt><dd>{$result["vulnerability"]}</dd>\n";
      }
      echo "      </dl>\n";

      # Loop over updates and display
      if (array_key_exists("updates", $result)) {
        $updates = $result["updates"];

        echo "<dl>\n";

        foreach ($updates as $k => $u) {
          echo "<dt>$k</dt>\n<dd>";

          if (array_key_exists("security", $u)) {
            echo "<span class=\"security\" title=\"From {$u["current"]} to {$u["security"]}\">Security</span>";
          }

          if (array_key_exists("standard", $u)) {
            echo "<span class=\"standard\" title=\"From {$u["current"]} to {$u["standard"]}\">Standard</span>";
          }

          if (array_key_exists("cves", $u)) {
            echo "CVEs: ";

            foreach (array_count_values($u["cves"]) as $cs => $cn) {
              echo "<span class=\"pakiti2cve ".strtolower($cs)."\">$cn $cs</span>";
            }

            "</span>";
          }
          echo "</dd>\n";
        }

        echo "</dl>\n";
        echo "<p><span class=\"time\">&#8634; ".prettytime(time() - $result["timestamp"])."</span></p>\n";
      }

      # Display any errors that were passed
      if (array_key_exists("errors", $result)) {
        $errors = $result["errors"];
        foreach ($errors as $e) {
          echo "<p class=\"warning\">$e</p>";
        }
      }
    } else {
      echo "<p class=\"warning\">No records found for host</p>";
    }
  }
}

return new pPakiti2();
