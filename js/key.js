
$('.key-dropdown').html('<li></li>');

// Note
if ($(".note")[0]){
    $('.key-dropdown').append('<li><span class="node unknown note"></span>Note</li>');
}

// Nagios
if ($(".downtime")[0]){
    $('.key-dropdown').append('<li><span class="node downtime"></span>Downtime</li>');
}
if ($(".warning")[0]){
    $('.key-dropdown').append('<li><span class="node unknown warning"></span>Warning</li>');
}
if ($(".critical")[0]){
    $('.key-dropdown').append('<li><span class="node unknown critical"></span>Critical Alarm</li>');
}
if ($(".down")[0]){
    $('.key-dropdown').append('<li><span class="node down"></span>Unreachable</li>');
}
if ($(".uninstantiated")[0]){
    $('.key-dropdown').append('<li><span class="node uninstantiated"></span>Uninstantiated</li>');
}



if ($(".full")[0]){
    $('.key-dropdown').append('<li><span class="node full"></span>Full</li>');
}
if ($(".inuse")[0]){
    $('.key-dropdown').append('<li><span class="node inuse"></span>Inuse</li>');
}
if ($(".offline")[0]){
    $('.key-dropdown').append('<li><span class="node offline"></span>Offline</li>');
}
if ($(".free")[0]){
    $('.key-dropdown').append('<li><span class="node free"></span>Free</li>');
}

if ($(".ReadOnly")[0]){
    $('.key-dropdown').append('<li><span class="node ReadOnly"></span>ReadOnly</li>');
}
if ($(".Ready")[0]){
    $('.key-dropdown').append('<li><span class="node Ready"></span>Ready</li>');
}
if ($(".Holding")[0]){
    $('.key-dropdown').append('<li><span class="node Holding"></span>Holding</li>');
}
if ($(".cloud-bad")[0]){
    $('.key-dropdown').append('<li><span class="node cloud-bad"></span>cloud-bad</li>');
}
if ($(".replica")[0]){
    $('.key-dropdown').append('<li><span class="node free replica"></span>replica</li>');
}
if ($(".Production")[0]){
    $('.key-dropdown').append('<li><span class="node Production"></span>Production</li>');
}
if ($(".Test")[0]){
    $('.key-dropdown').append('<li><span class="node Test"></span>Test</li>');
}
