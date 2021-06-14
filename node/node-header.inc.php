<?php global $NODE; ?>
<div class="head">
    <h1>Host: <span class="host-name"><?php echo htmlspecialchars($NODE) . "\n" ?></span></h1>
    <a class="tab" href="ssh://root@<?php echo $NODE ?>">SSH</a>
    <?php
    //get number from hostname
    preg_match('/[0-9]+/', $NODE, $number);

    if (is_array($number) and sizeof($number) > 0) {
        $number = $number[0];
        $number_digits = strlen($number); //SF's
    }
    else {
        $number = null;
        $number_digits = 0;
    }

    //get hostname without number
    $name_parts = preg_split('/[0-9]+/', $NODE);

    //output next-door nodes
    if (count($name_parts) >= 2) {
        $prev_machine = $name_parts[0].sprintf("%0${number_digits}s", $number - 1).$name_parts[1];
        $next_machine = $name_parts[0].sprintf("%0${number_digits}s", $number + 1).$name_parts[1];

        if ($number_digits > 0) {
            if ($number > 0) {
                echo '<a class="tab" href="node.php?n='.$prev_machine.'" title="'.$prev_machine.'"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>';
            }
            echo '<a class="tab" href="node.php?n='.$next_machine.'" title="'.$next_machine.'"><span class="glyphicon glyphicon-circle-arrow-right"></span></a>';
        }
    }
    ?>

</div>
