function updateKey() {

    $('.key').empty();

    var keyArray = {
        "Note" : "note",
        "Downtime" : "downtime",
        "Warning" : "warning",
        "Critical" : "critical",
        "Down" : "down",
        "Unreachable" : "unreachable",
        "Uninstantiated" : "uninstantiated",
        "Full" : "full",
        "Inuse" : "inuse",
        "Offline" : "offline",
        "Free" : "free",
        "ReadOnly" : "readOnly",
        "Ready" : "ready",
        "Holding" : "holding",
        "Replica" : "replica",
        "Production" : "production",
        "Draining" : "draining",
        "Decomissioned" : "decomissioned",
        "Started" : "started",
        "Relocating" : "relocating",
        "Unassigned" : "unassigned",
        "Initializing" : "initializing",
    };

    $.each( keyArray, function( key, value ) {
        var node = $('.'+value);
        if (node[0]) {
            $('.key').append('<li class="key-item"><span class="node unknown '+value+'"></span>&nbsp;'+key+'</li>');
        }
    });
}
