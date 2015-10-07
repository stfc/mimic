function updateKey() {
    $('.key').html('');

    if ($(".note")[0]){
        $('.key').append('<li class="key-item"><span class="node unknown note"></span>Note</li>');
    }
    if ($(".downtime")[0]){
        $('.key').append('<li class="key-item"><span class="node downtime"></span>Downtime</li>');
    }
    if ($(".warning")[0]){
        $('.key').append('<li class="key-item"><span class="node unknown warning"></span>Warning</li>');
    }
    if ($(".critical")[0]){
        $('.key').append('<li class="key-item"><span class="node unknown critical"></span>Critical Alarm</li>');
    }
    if ($(".down")[0]){
        $('.key').append('<li class="key-item"><span class="node down"></span>Unreachable</li>');
    }
    if ($(".uninstantiated")[0]){
        $('.key').append('<li class="key-item"><span class="node uninstantiated"></span>Uninstantiated</li>');
    }
    if ($(".full")[0]){
        $('.key').append('<li class="key-item"><span class="node full"></span>Full</li>');
    }
    if ($(".inuse")[0]){
        $('.key').append('<li class="key-item"><span class="node inuse"></span>Inuse</li>');
    }
    if ($(".offline")[0]){
        $('.key').append('<li class="key-item"><span class="node offline"></span>Offline</li>');
    }
    if ($(".free")[0]){
        $('.key').append('<li class="key-item"><span class="node free"></span>Free</li>');
    }
    if ($(".ReadOnly")[0]){
        $('.key').append('<li class="key-item"><span class="node ReadOnly"></span>ReadOnly</li>');
    }
    if ($(".Ready")[0]){
        $('.key').append('<li class="key-item"><span class="node Ready"></span>Ready</li>');
    }
    if ($(".Holding")[0]){
        $('.key').append('<li class="key-item"><span class="node Holding"></span>Holding</li>');
    }
    if ($(".replica")[0]){
        $('.key').append('<li class="key-item"><span class="node free replica"></span>replica</li>');
    }
    if ($(".Production")[0]){
        $('.key').append('<li class="key-item"><span class="node Production"></span>Production</li>');
    }
    if ($(".Draining")[0]){
        $('.key').append('<li class="key-item"><span class="node Draining"></span>Draining</li>');
    }
    if ($(".Decomissioned")[0]){
        $('.key').append('<li class="key-item"><span class="node Decomissioned"></span>Decomissioned</li>');
    }
    if ($(".Test")[0]){
        $('.key').append('<li class="key-item"><span class="node Test"></span>Test</li>');
    }
}
