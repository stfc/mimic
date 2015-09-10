// Pops open the info box for a specific node
var node_window = null;

function node(node_name) {
    node_window = window.open("node.php?n="+node_name, "node", "width=640,height=480,left=128,top=128,resizable=yes,scrollbars=yes,directories=no,titlebar=no,toolbar=no,status=no"); node_window.window.focus();
}

function updatePos() {
    node_window = window.open("positions.php", "updater", "width=320,height=240,left=128,top=128,resizable=no,scrollbars=no,directories=no,titlebar=no,toolbar=no,status=no"); node_window.window.focus();
}

// Node search
function locateNode(name) {
    $("span").css("outline", '');
    $("span").css("zIndex", '');

    if (name !== null && name.length > 0) {
        name = name.replace(",", " ");
        name = name.split(" ");
        var node_name;
        for (node_name in name) {
            if (name[node_name] !== null) {
                var node = $("span[id^='n_"+name[node_name]+"']");
                node.css("outline", "3px solid #FFFF00");
                node.css("zIndex", "1");
            }
        }
    }
}
