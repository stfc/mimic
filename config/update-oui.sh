#!/bin/bash

echo '<?php' > oui.inc.php
echo '$ouilist = array(' >> oui.inc.php
curl -s "http://standards.ieee.org/develop/regauth/oui/oui.txt" | grep "(base 16)" | sed 's!"!\&quot;!g' | sed 's!^\([0-9a-fA-F]\+\)\s\+(base 16)\s\+\(.\+\)\+$!"\1" => "\2",!g' >> oui.inc.php
echo ');' >> oui.inc.php
echo '?>' >> oui.inc.php
