//Pops open the info box for a specific node
var nodewin = null;

function node(n) {
  nodewin = window.open("views/node/node.php?n="+n, "node", "width=640,height=480,left=128,top=128,resizable=yes,scrollbars=yes,directories=no,titlebar=no,toolbar=no,status=no"); nodewin.window.focus();
}

function updatePos() {
  nodewin = window.open("positions.php", "updater", "width=320,height=240,left=128,top=128,resizable=no,scrollbars=no,directories=no,titlebar=no,toolbar=no,status=no"); nodewin.window.focus();
}

function locateNode(name) {
  $("span").css("outline", '');
  $("span").css("zIndex", '');

  if (name !== null && name.length > 0) {
    name = name.replace(",", " ");
    name = name.split(" ");
    var n;
    for (n in name) {
      if (name[n] !== null) {
        var node = $("span[id^='n_"+name[n]+"']")
        node.css("outline", "3px solid #FFFF00");
        node.css("zIndex", "1");
      }
    }
  }
}
