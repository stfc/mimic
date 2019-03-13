<?php

class pBatchSystem
{

  function header()
  {
    return("Batch System State");
  }

  function detail($node, $short, $jobId = null, $jobCount = null)
  {
    global $SQL;
    // Look for state sources, and display them
    $got = $SQL->query("select source, state, info, unix_timestamp() - unix_timestamp(time) as 'update' from state where name = '$node'"); #, unix_timestamp() - unix_timestamp(time)
    if ($got != null and $got->num_rows) {
      while ($row = $got->fetch_row()) {
        $source = $row[0];
        $state  = $row[1];
        $info   = $row[2];
        $update = $row[3];
        $state_visual = "";

        //Get first chunk of source, indicating batch system type
        $source_a    = explode(" on ", $source);
        $source_type = $source_a[0]; #TODO: Bound checking in case the source field is non-standard
        $scheduler   = $source_a[1];

        echo "      <dl class=\"dl-horizontal\">\n";
        echo "        <dt>Source</dt><dd>".htmlspecialchars($source)."</dd>\n";
        echo "        <dt>State</dt><dd>".$state."</dd>\n";

        list($jtit, $jobs, $ctit, $slots, $ptit, $prop) = explode(" ",$info);

        echo "      <dt>Job Slots</dt><dd>$slots</dd>\n";
        echo "        <dt>Jobs</dt>\n";
        echo "        <dd>";

        $job_number = 0;

        if ($jobs != '[none]') {
          echo "$jobs";
          $job_number = (int)$jobs;
        }
        echo "</dd>\n";
        $dead = "";
        if ($state == "offline" or $state == "down") {
          $dead = "dead";
        }
        for($i = 0; $i < $job_number; $i++) {
          $state_visual = $state_visual . '<div class="cpu '.$dead.'inuse"></div>';
        }
        for($i = 0; $i < ($slots - $job_number); $i++) {
          $state_visual = $state_visual . '<div class="cpu '.$dead.'free"></div>';
        }
        echo "         <dt>Visual State</dt><dd>$state_visual</dd>\n";

        echo    "      <dt>Properties</dt><dd>".$prop."</dd>\n";

        echo    "    </dl>\n";
        echo    "    <p><span class=\"time\">&#8634;".prettytime($update)."</span></p>\n";
      }
    }
    else {
      echo "<p class=\"info\">No info for host.</p>\n";
    }
  }
}

return new pBatchSystem();
