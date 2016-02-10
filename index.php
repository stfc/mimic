<?php require_once('header.php'); ?>

<!-- Main content -->
<div class="wrapper">
    <div class="loading"><img src="assets/dist/images/loading.svg" alt="loading" /></div>
    <div id="farm"><!-- Nodes get rendered in here --></div>
</div>

<script type="text/javascript">

if (Cookies.get('view')) {
    view(Cookies.get('view'));
} else {
    view('logical-workers');
}

function view(requested_view) {
    $('.loading').show();   // Overlay loading svg
    $('.tab').removeClass('active');   // Clear all tabs so none show as selected
    Cookies.set('view', requested_view);   // Set the requested view as a cookie

    current_view = Cookies.get('view');
    $('#' + current_view).addClass('active');
    getData(current_view);
}

function getData(current_view) {
    $.ajax({
        dataType: "json",
        url: 'views/view-' + current_view + '.php',
        cache: false
    }).done(gotData);
}


$grid = $('#farm').masonry({
    gutter: 16,
    itemSelector: '.grid-item',
    columnWidth: 228,
    transitionDuration: 0
});







function gotData(data) {
    $('.loading').hide();
    var finished_node = "";

    for (group_name in data) {
        group_data = data[group_name];
        console.log('[group] ' + group_name);

        finished_node += "<div class='node-group' id='"+group_name+"'>";
        if (group_name !== "") {
            finished_node += "<h2 class='group-name  grid-item'>"+group_name+"</h2>";
        }
        for (panel_name in group_data) {
            panel_data = group_data[panel_name];
            console.log('    [panel] ' + panel_name);


            finished_node += "<div class='node-panel grid-item' id='"+panel_name+"'>";
            finished_node += "<h3 class='panel-name'>"+panel_name+"</h3>";

            for (cluster_name in panel_data) {
                cluster_data = panel_data[cluster_name];
                console.log('        [cluster] ' + cluster_name);

                if (cluster_name !== "") {
                    finished_node += "<h5 class='cluster-name'>"+cluster_name+"</h5>";
                }

                for (node_name in cluster_data) {
                    // console.log(cluster_data.length);

                    node_data = cluster_data[node_name];
                    console.log('            [node] ' + node_name);
                    node_info = "<h4>"+node_name+"</h4>";

                    if (("status" in node_data) && ("state" in node_data.status)) {
                        node_status = node_data.status.state;
                        node_info += "<p><b>State:</b> "+node_status+"</p>";
                    } else {
                        node_status = "unknown";
                    }

                    if (("status" in node_data) && ("source" in node_data.status)) {
                        node_info += "<p><b>Source:</b> "+node_data.status.source+"</p>";
                    }

                    if (("note" in node_data)) {
                        node_status += " note";
                        note = node_data.note;
                        node_info += "<p><b>Note:</b> &quot;"+note+"&quot;</p>"
                    }

                    finished_node += '<span id="'+node_name+'" class="node '+node_status+'" title="'+node_info+'"></span>';
                }
            }
            finished_node += "</div>";
        }
        finished_node += "</div>";
    }

    $('#farm').empty();

    $grid.prepend( finished_node ).masonry( 'reloadItems' );
    $grid.masonry();

    $('.node').click(function() {
        open_node(this.id)
    });

}










var node_window = null;

function open_node(node_name) {
    node_window = window.open("node.php?n="+node_name, "node", "width=640,height=480,left=128,top=128,scrollbars=yes,toolbar=no,status=no");
    node_window.window.focus();
}

// ------------------------------------------
// Makes things look nice                  f3
// ------------------------------------------
function layout() {

    // Node search
    locateNode($('#inLocate').val());

    // Node tooltip
    $('.node, .page-error').tooltip({
        html: true,
        container: '#farm',
        placement: 'auto',
        delay: {"show": 100}
    });

    // Runs from js/key.js
    updateKey();

    // Increases width of panel if number of nodes is too high
    // $.each($('.node-cluster'), nodeCount);
}

// ------------------------------------------
// Counts nodes and does stuff             f4
// ------------------------------------------
function nodeCount() {

    // If a cluster has too many nodes this will apply the 'grid-item-big' class to it


    // Adds title with number of nodes in
    // if ($(".cluster-name")[0]) {
    //     $(this).children(".cluster-name").prop('title', "This section contains " + $con_width + " nodes");
    // } else {
    //     $(this).prev().prop('title', "This section contains " + $con_width + " nodes");
    // }

}

$('.tab').click(function () {
    view(this.id);
});

// loading();
getData();
// window.setInterval(getData, 20000); // Every 60 seconds

</script>
<?php require_once('footer.php'); ?>
