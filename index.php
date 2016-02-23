<?php require_once('header.php'); ?>

<!-- Main content -->
<div class="wrapper">
    <div class="loading"><img src="assets/dist/images/loading.svg" alt="loading" /></div>
    <div id="farm"><!-- Nodes get rendered in here --></div>
</div>

<script type="text/javascript">
// Initialise masonry
$grid = $('#farm').masonry({
    gutter: 16,
    itemSelector: '.grid-item',
    columnWidth: 228,
    transitionDuration: 0
});

run_view();   // Get content on page load
window.setInterval(run_view, 60000);   // Then every 60 seconds

function run_view() {
    if (Cookies.get('view')) {
        current_view = Cookies.get('view');
    } else {
        current_view = 'logical-workers';   // Set default view to load if no cookie found
    }

    $('#' + current_view).addClass('active');
    getData(current_view);
}

// Run when menu tab clicked
function view(requested_view) {
    $('.loading').show();   // Overlay loading svg
    $('.tab').removeClass('active');   // Clear all tabs so none show as selected
    Cookies.set('view', requested_view);   // Set the requested view as a cookie
    run_view();
}

function getData(current_view) {
    requested_view = current_view;
    $.ajax({
        dataType: 'json',
        url: 'views/view-' + current_view + '.php',
        cache: false
    }).done(function(d) {
        if (current_view != requested_view) {
            console.log("Ignoring callback for "+requested_view+", current view is "+current_view);
            return;
        }
        gotData(d);
    });
}

function gotData(data) {
    $('.loading').hide();
    finished_node = '';   // Final output

    for (group_name in data) {
        group_data = data[group_name];
        finished_node += '<div class="node-group" id="' + group_name + '">';
        if (group_name !== "") {
            finished_node += "<h2 class='group-name  grid-item'>"+group_name+"</h2>";
        }

        for (panel_name in group_data) {
            panel_data = group_data[panel_name];
            new_box_size = '';

            if (group_name !== "sandbox") {
                for (cluster_name in panel_data) {
                    length = Object.keys(panel_data[cluster_name]).length;
                    if (length > 360) {
                        new_box_size += ' step1';
                    }
                }
            }

            finished_node += '<div class="node-panel grid-item' + new_box_size + '" id="' + panel_name + '">';
            finished_node += '<h3 class="panel-name">' + panel_name + '</h3>';

            for (cluster_name in panel_data) {
                cluster_data = panel_data[cluster_name];

                if (current_view === 'elasticsearch-routing') {
                    finished_node += '<div class="rack">';
                } else {
                    finished_node += '<div class="node-cluster">';
                }

                if (cluster_name !== '') {
                    finished_node += '<h5 class="cluster-name">' + cluster_name + '</h5>';
                }

                if (cluster_data.length !== 0) {
                    for (node_name in cluster_data) {
                        node_data = cluster_data[node_name];
                        node_info = '<h4>' + node_name + '</h4>';

                        node_status = 'unknown';

                        for (info in node_data) {
                            info_body = '';

                            if (info === 'status') {
                                info = '<h4>Status:</h4>';
                                node_status = "";
                                for (status in node_data['status']) {
                                    node_status += status;
                                }
                                info_body += '<ul><b>' + node_status.toLowerCase() + '</b> - ' + node_data['status'][status] + '</ul>';
                            } else {
                                info_body = node_data[info];
                                info = '<b>' + info.replace(/_/g, ' ') + '</b>: ';
                            }
                            node_info += '<p>' + info + info_body +'</p>';
                        }

                        if (node_data['note']) {
                            node_status += ' note';
                        }

                        if (node_data['nagios']) {
                            node_status += node_data['nagios'];
                        }

                        // Node
                        finished_node += '<span id="' + node_name + '" class="node ' + node_status.toLowerCase() + '" title="' + node_info + '"></span>';
                    }
                }
                else {
                    finished_node += '<span title="No managed systems">&nbsp;&#x2205;</span>';
                }
                finished_node += '</div>';
            }
            finished_node += '</div>';
        }
        finished_node += '</div>';
    }

    $('#farm').empty();

    $grid.prepend( finished_node ).masonry( 'reloadItems' );
    $grid.masonry();

    // Node search
    locateNode($('#inLocate').val());

    // Node tooltip
    $('.node, .page-error').tooltip({
        html: true,
        container: '#farm',
        placement: 'auto',
        delay: {"show": 100}
    });

    // Node window
    if (current_view !== 'elasticsearch-routing' && current_view !== 'elasticsearch-hosts') {
        $('.node').click(function() {
            open_node(this.id);
        });
    }

    // Runs from js/key.js
    updateKey();
}
</script>
<?php require_once('footer.php'); ?>
