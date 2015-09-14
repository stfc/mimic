<?php

class pBatchSystem
{

  function header()
  {
    return("Batch System State");
  }

  function detail($node, $short, $jobId = null, $jobCount = null)
  {
    // Look for state sources, and display them
    $got = mysql_query("select source, state, info, unix_timestamp() - unix_timestamp(time) as 'update' from state where name = '$node'"); #, unix_timestamp() - unix_timestamp(time)
    if ($got != null and mysql_num_rows($got)) {
      while ($row = mysql_fetch_row($got)) {
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

        if (($source_type == 'PBS' or $source_type == 'HTCondor') and $info) {
          list($jtit, $jobs, $ctit, $slots, $ptit, $prop) = explode(" ",$info);

          echo "      <dt>Job Slots</dt><dd>$slots</dd>\n";
          echo "        <dt>Jobs</dt>\n";
          echo "        <dd>";

          $job_number = 0;

          if ($jobs != '[none]'){
            if ($source_type == 'HTCondor') {
              echo "$jobs";
              $job_number = (int)$jobs;
            } else {
              $jobs = explode (',', $jobs);
              $job_number = sizeof($jobs);

              echo "\n          <ul class=\"state-jobs\">\n";

              $sjobs = ''; //< String list of jobs for this node

              foreach ($jobs as $job) {
                $sjobs .= $job . '_'; //< Add job to "all" list

                echo "            <li><a class=\"job\" onclick=\"jobInfo('scheduler=$scheduler&amp;n=$node&amp;jobid=$job&amp;level=full');\">$job</a></li>\n";
              }

              $sjobs = substr($sjobs, 0, -1); //< Trim trailing _

              echo "            <li><a onclick=\"jobInfo('scheduler=$scheduler&amp;n=$node&amp;jobid=$sjobs&amp;level=summary');\">Summary of All Jobs</a></li>\n";
              echo "          </ul>\n         ";
            }
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
        }
        elseif ($source_type == 'LSF') {
          echo "        <dt>Jobs</dt><dd>LSF job IDs not yet implemented</dd>\n";
        }
        else {
          echo "        <dt>Jobs</dt><dd>Not implemented</dd>\n";
        }

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
