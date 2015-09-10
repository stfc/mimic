<?php

class pNotes
{

    function header()
    {
        $header = Array("Notes");
        return($header);
    }

    function detail($NODE, $SHORT)
    {
        ?>
        <div id="node-notesdiv" title="Edit Notes" style="display: none">
            <form method="post" action="node.php?n=<?php echo htmlspecialchars($NODE) ?>">
                <textarea id="node-inputnote" name="note" rows="6"></textarea>
            </form>
        </div>
        <p id="node-notestext"><span class="loading">Loading...</span></p>
        <p><span id="node-notestime" class="time"></span></p>
        <script type="text/javascript">

            // Runs when page has loaded
            $(function() {
                $("#node-notesdiv").dialog({
                    autoOpen: false,
					modal: true,
                    buttons: {
                        "Delete" : function() {
                            noteUpdate("");
                            $("#node-notesdiv").dialog("close");
                        },
                        "Update" : function() {
                            noteUpdate($("#node-inputnote").val());
                            $("#node-notesdiv").dialog("close");
                        },
                    }
                });
                noteRefresh();
            });

            // Callback for AJAX requests
            function noteCallbackDisplay(d, s, x) {
                var data = $.parseJSON(d);
                var edit = '<a class="edit" onclick="$(\'#node-notesdiv\').dialog(\'open\');"></a>';
                if (data["status"] == "ok") {
                    $("#node-notestext").html(data["note"] + edit);
                    $("#node-inputnote").val(data["note"]);
                }
                else {
                    $("#node-notestext").html('<p class="error">API Failure</p>');
                    $("#node-inputnote").val("");
                }
                $("#node-notestime").html("&#8634&nbsp;" + data["time"]);
            }

            // Get current note
            function noteRefresh() {
                var args = {
                    "node" : "<?php echo $NODE; ?>",
                    "time" : Date.now()
                };
                $.get("/components/api-notes.php", args, noteCallbackDisplay);
            }

            // Update note (delete with empty string)
            function noteUpdate(text) {
                var args = {
                    "node" : "<?php echo $NODE; ?>",
                    "time" : Date.now(),
                    "note" : text
                };
                $.get("/components/api-notes.php", args, noteCallbackDisplay);
            }

        </script>
        <?php
    }
}

return new pNotes();
