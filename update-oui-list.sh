#!/bin/bash
echo ''
echo '#################################################################'
echo 'Generating oui-list.php in config/ --- This may take some time...'
echo '#################################################################'
echo ''
echo 'Working...'
echo '<?php' > config/oui-list.php
echo '$ouilist = array(' >> config/oui-list.php
curl -s "http://standards-oui.ieee.org/oui/oui.txt" | grep "(base 16)" |  sed 's!"!\&quot;!g' | sed 's!^\([0-9a-fA-F]\{6\}\)\s\+(base 16)\s\+\(.\+\)\r$!"\1"=>"\2",!g' | tr -d '\n' >> config/oui-list.php
echo ');' >> config/oui-list.php
echo ''
echo '#################################################################'
echo '                             Done!'
echo '#################################################################'
