<?php

class pSpec
{
  function header($node, $short)
  {
    return("System Details");
  }

  function detail($NODE, $SHORT)
  {
    //Look for node spec info
    $got = mysql_query("select os, cpus, speed, memory, disk, pbsscale, mac, date_format(time, '%e/%c/%Y at %H:%i'), INET_NTOA(ipmi_ip) from nodeinfo where name='".mysql_escape_string($NODE)."'");
    if ($got and mysql_num_rows($got)) {
      $r = mysql_fetch_row($got);

    echo "      <dl>\n";
    if ($r[1] != "") echo "        <dt>CPUs</dt><dd>".$r[1]."x ".$r[2]."MHz</dd>\n";
    if ($r[3] != "") echo "        <dt>RAM</dt> <dd>".$r[3]."MB</dd>\n";
    if ($r[4] != "") echo "        <dt>Disk</dt><dd>".$r[4]."GB</dd>\n";
    if ($r[0] != "") echo "        <dt>OS</dt>  <dd>".$r[0]."</dd>\n";
    if ($r[6] != "") echo "        <dt>MAC</dt> <dd>".$r[6]."</dd>\n";
    if ($r[5] != "") echo "        <dt>PBS scale factor</dt><dd>{$r[5]}</dd>\n";
    if ($r[8] != "") echo "        <dt>IPMI IP Address</dt><dd><a href=\"http://{$r[8]}/\">{$r[8]}</a></dd>\n";
    echo "      </dl>\n";
    if ($r[7] != "") echo "      <p><span class=\"time\">&#8634;{$r[7]}</span></p>\n";
    }
    else {
      echo "      <p class=\"warning\">Unknown</p>\n";
    }
  }
}

return new pSpec();

?>
