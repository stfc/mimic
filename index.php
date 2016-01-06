<?php require_once('header.php'); ?>

<!-- Main content -->
<div class="wrapper">
    <div id="farm"><!-- Nodes get rendered in here --></div>
</div>

<script type="text/javascript">
// ------------------------------------------
// Timer to show when view was last updated
// ------------------------------------------
var counter = 0;
setInterval(function () {
    ++counter;
    $('.time').html('<p>Updated '+counter+' seconds ago');
}, 1000);

// ------------------------------------------
// Loading screen
// ------------------------------------------
$loading = '<div class="loading"><img src="assets/dist/images/loading.svg" alt="loading" /></div>';
function loading() {
    $('#farm').html($loading);
}

// ------------------------------------------
// Sets home view
// ------------------------------------------
var current_view = 'logical-workers';
$('#' + current_view).addClass('active');

// ------------------------------------------
// Makes Ajax call                         f1
// ------------------------------------------
function getData() {
    $.ajax({
        url: 'views/view-' + current_view + '.php',
        cache: false
    })
    .done(gotData);
}

// ------------------------------------------
// Inserts getData into page               f2
// ------------------------------------------
function gotData(data) {

    // Inserts fetched data into page
    $( "#farm" ).html(data);

    // Runs layout
    layout();

    // Sets "last updated" timer back to 0
    counter = 0;
}

// ------------------------------------------
// init Masonry
// ------------------------------------------
function masonry() {
    $('.node-group').masonry({
        gutter: 16,
        itemSelector: '.grid-item',
        columnWidth: 228
    });
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
    $.each($('.node-cluster'), nodeCount);

    // Runs masonry
    masonry();

}

// ------------------------------------------
// Counts nodes and does stuff             f4
// ------------------------------------------
function nodeCount() {

    // If a cluster has too many nodes this will apply the 'grid-item-big' class to it
    $con_width = $(this).children('.node').length;
    if ($con_width > 240) {
        $(this).parent().addClass('grid-item-big');
    }

    // Adds title with number of nodes in
    if ($(".cluster-name")[0]) {
        $(this).children(".cluster-name").prop('title', "This section contains " + $con_width + " nodes");
    } else {
        $(this).prev().prop('title', "This section contains " + $con_width + " nodes");
    }

}

loading();
getData();
window.setInterval(getData, 60000); // Every 60 seconds

</script>
<?php require_once('footer.php'); ?>
