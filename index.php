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
window.setInterval(run_view, 10000);   // Then every 60 seconds

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
    $.ajax({
        dataType: 'json',
        url: 'views/view-' + current_view + '.php',
        cache: false
    }).done(gotData);
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
            finished_node += '<div class="node-panel grid-item" id="' + panel_name + '">';
            finished_node += '<h3 class="panel-name">' + panel_name + '</h3>';

            for (cluster_name in panel_data) {
                cluster_data = panel_data[cluster_name];
                if (cluster_name !== '') {
                    finished_node += '<h5 class="cluster-name">' + cluster_name + '</h5>';
                }

                for (node_name in cluster_data) {
                    node_data = cluster_data[node_name];
                    node_info = '<h4>' + node_name + '</h4>';

                    // Status
                    if (('status' in node_data) && ('state' in node_data.status)) {
                        node_status = node_data.status.state;
                        node_info += '<p><b>State:</b> ' + node_status + '</p>';
                    } else {
                        node_status = 'unknown';
                    }

                    // Source
                    if (('status' in node_data) && ('source' in node_data.status)) {
                        node_info += '<p><b>Source:</b> ' + node_data.status.source + '</p>';
                    }

                    // Note
                    if (('note' in node_data)) {
                        node_status += ' note';
                        node_info += '<p><b>Note:</b> &quot;' + node_data.note + '&quot;</p>';
                    }

                    // Node
                    finished_node += '<span id="' + node_name + '" class="node ' + node_status + '" title="' + node_info + '"></span>';
                }
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
    $('.node').click(function() {
        open_node(this.id);
    });

    // Runs from js/key.js
    updateKey();
}
</script>
<?php require_once('footer.php'); ?>
