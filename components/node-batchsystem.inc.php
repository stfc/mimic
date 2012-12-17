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
      while ($r = mysql_fetch_row($got)) {
        $source = $r[0];
        $state  = $r[1];
        $info   = $r[2];
        $update = $r[3];
        $state_visual = "";

        //Get first chunk of source, indicating batch system type
        $source_a    = explode(" on ", $source);
        $source_type = $source_a[0]; #TODO: Bound checking in case the source field is non-standard
        $scheduler   = $source_a[1];

        echo "      <dl>\n";
        echo "        <dt>Source</dt><dd>".htmlspecialchars($source)."</dd>\n";
        echo "        <dt>State</dt><dd>".$state."</dd>\n";

        if ($source_type == 'PBS' and $info) {
          list($jtit, $jobs, $ctit, $slots, $ptit, $prop) = explode(" ",$info);

          echo "      <dt>Job Slots</dt><dd>$slots</dd>\n";
          echo "        <dt>Jobs</dt>\n";
          echo "        <dd>\n";

          $job_number = 0;

          if ($jobs != '[none]'){
            $jobs = explode (',', $jobs);
            $job_number = sizeof($jobs);
            echo "          <ul class=\"state-jobs\">\n";

            $sjobs = ''; //< String list of jobs for this node

            foreach ($jobs as $job) {
              $lc = '';

              $sjobs .= $job . '_'; //< Add job to "all" list

              if (isset($jobId)) {
                if ($job == $jobId) {
                  $lc = ' class = "active"';
                }
              }
              echo "            <li><a class=\"job\" href=\"batch-job-info.php?scheduler=$scheduler&amp;n=$node&amp;jobid=$job&amp;level=full\"$lc>$job</a></li>\n";
            }

            $sjobs = substr($sjobs, 0, -1); //< Trim trailing _

            //Are we looking at multiple (ie. ALL jobs) on a node?
            $lc = '';
            if ($jobCount > 1) {
              $lc = ' class = "active"';
            }

            echo "            <li><a href=\"batch-job-info.php?scheduler=$scheduler&amp;n=$node&amp;jobid=$sjobs&amp;level=summary\"$lc>Summary of All Jobs</a></li>\n";
            echo "           </ul>\n";
          }
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
          echo "         </dd>\n";
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
?>
