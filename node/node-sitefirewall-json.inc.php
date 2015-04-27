<?php

class pSiteFirewall
{

  function header()
  {
    return("Site Firewall");
  }

  function detail($NODE, $SHORT)
  {
    $FIREWALL_CACHE_FILE = "cache/parsed-fw-data.json";

    if (file_exists($FIREWALL_CACHE_FILE)) {
      $fw = file_get_contents("cache/parsed-fw-data.json");
      $fw = str_replace("'", '"', $fw);
      $fw = json_decode($fw, True);

      if (is_array($fw)) {
        if (isset($fw["group_addresses"][$NODE])) {
          echo "<p>Member of groups:</p>\n";
          echo "<ul>\n";
          foreach ($fw["group_addresses"][$NODE] as $g) {
            echo "<li>$g</li>\n";
  //          echo "<li>{$fw["groups"][$g]}</li>\n";
          }
          echo "</ul>\n";
        }
        else {
          echo "<p class=\"info\">Not a member of any groups</p>\n";
        }
      } else {
        echo "<p class=\"warning\">No records found for host</p>\n";
      }
    } else {
      echo "<p class=\"error\">Firewall cache file not present</p>\n";
    }
  }
}

return new pSiteFirewall();

?>
