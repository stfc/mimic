<?php
require("header.php"); // Important includes

// Gathers clusters
$all_clusters = Array();
$all_nodes = pg_query("select \"systemHostname\" as \"name\", \"categoryName\", \"rackId\", \"systemRackPos\" "
    ."from \"vBuildTemplate\" "
    ."where \"systemHostname\" not like '%.internal'"
    ."order by \"categoryName\", \"systemHostname\";");
if ($all_nodes and pg_num_rows($all_nodes)){
    while ($row = pg_fetch_assoc($all_nodes)) {
        $all_clusters[$row['name']] = Array(
            'panel' => $row['categoryName'],
            );
    }
}

// Gathers notes
$all_notes = Array();
$notes = mysql_query("select name, note from notes");
if ($notes and mysql_num_rows($notes)) {
    while ($note = mysql_fetch_assoc($notes)) {
        $all_notes[$note['name']] = $note['note'];
    }
}

// Generates main array
generational_results($all_clusters, $all_notes);
