<?php

class pObservium
{
  function header()
  {
    return("Observium LLDP");
  }

  function detail($NODE, $SHORT)
  {
    $DB_OBS_HANDLE = mysql_pconnect("observium.example.com", "observium", "observium")
      or trigger_error("Unable to open observium database connection", E_USER_ERROR);

  if ($DB_OBS_HANDLE) {
      $query_lldp = "select remote_hostname, hostname, ifName from observium.links join observium.ports on local_interface_id = interface_id join observium.devices on ports.device_id = devices.device_id where active = 1 and remote_hostname = '$NODE';";
#	  echo "<pre>$query_lldp</pre>";
      $got = mysql_query($query_lldp, $DB_OBS_HANDLE);
      if ($got and mysql_num_rows($got)) {
#	    echo "<p>Seen by LLDP on the following ports</p>\n";
	    echo "<dl>\n";
        while ($r = mysql_fetch_row($got)) {
          echo "<dt>{$r[1]}</dt><dd>{$r[2]}</dd>\n";
        }
	    echo "</dl>\n";
      } else {
	    echo "<p class=\"info\">No LLDP neighbours found.</p>";
	  }
    }
  }
}

return new pObservium();

?>
