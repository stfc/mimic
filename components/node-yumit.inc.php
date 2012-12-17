<?php

class pYumit
{
  function detail($NODE, $SHORT)
  {
    global $DB_YUMIT_NAME;
    global $DB_YUMIT_HANDLE;

    echo "    <div class=\"sub\" id=\"yumit\">\n";
    echo "      <h2>Yumit</h2>\n";
 
    $got = mysql_query("select time, host, admin, os, kernel from $DB_YUMIT_NAME.host where $DB_YUMIT_NAME.host.host like '".mysql_escape_string($NODE)."%'", $DB_YUMIT_HANDLE);
    $t   = mysql_fetch_row($got);

    if ($got and mysql_num_rows($got)) {
      $krn = $t[4];
      $os  = $t[3];
      $adm = $t[2];
      $h   = $t[1];
      $t   = $t[0];
  
      $got = mysql_query("select name, version from $DB_YUMIT_NAME.updates JOIN $DB_YUMIT_NAME.pkg ON $DB_YUMIT_NAME.updates.pkgid=$DB_YUMIT_NAME.pkg.id where host = '".mysql_escape_string($h)."' order by name", $DB_YUMIT_HANDLE);
      if ($got and mysql_num_rows($got)) {
        echo "      <dl>\n";
        echo "        <dt>Admin</dt><dd>$adm</dd>\n";
        echo "        <dt>OS</dt><dd>$os</dd>\n";
        echo "        <dt>Kernel</dt><dd>$krn</dd>\n";
        echo "      </dl>\n";
        echo "      <p>The following ".mysql_num_rows($got)." packages are out of date:</p>\n";
        echo "        <ul class=\"yumit-packages\">\n";
        while ($r = mysql_fetch_row($got)) {
          $package = $r[0];
           $version = $r[1];
          if (strpos($package, 'kernel') !== false) {
            echo "          <li class=\"kernel\">$package</li>\n";
          }
          else {
            echo "          <li>$package</li>\n";
          }
        }
        echo "        </ul>\n";
      }
      else {
        echo "      <p class=\"info\">No out-of-date packages listed.</p>\n";
      }
      if ($t != null) {
        echo "      <p><span class=\"time\">&#8634; $t</span></p>\n";
      }
    }
    else {
      echo "      <p class=\"warning\"No record of this host found!</p>\n";
    }
    echo "    </div>\n";
  }
}

?>
