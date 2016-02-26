<script type="text/javascript">
    // Node window
    node_window = null;
    function open_node(node_name) {
        node_window = window.open("node.php?n="+node_name, "node", "width=640,height=480,left=128,top=128,scrollbars=yes,toolbar=no,status=no");
        node_window.window.focus();
    }

    // Allows user to search
    function locateNode(name) {
        $("span").css("outline", '');
        $("span").css("zIndex", '');

        if (name !== null && name.length > 0) {
            name = name.replace(",", " ");
            name = name.split(" ");
            var node_name;
            for (node_name in name) {
                if (name[node_name] !== null) {
                    var node = $("span[id^='"+name[node_name]+"']");
                    node.css("outline", "3px solid #FFFF00");
                    node.css("zIndex", "1");
                }
            }
        }
    }

    // Looks for typing
    $('#inLocate').keyup(function () {
        locateNode(this.value);
    });

    // Shows and hides menu tabs
    $('.drop-head').click(function () {
        $(this).children('span').toggleClass('glyphicon-circle-arrow-right').toggleClass('glyphicon-circle-arrow-down');
    });

    // Sets cookie on click
    $('.tab').click(function () {
        view(this.id);
    });

    $('.scroll').mouseenter(function () {
        $('.scroll').css('overflow-y', 'auto');
    }).mouseleave(function () {
        $('.scroll').css('overflow-y', 'hidden');
    });

    // Cookie compliance
    $(document).ready(function(){
        $.cookieBar({
            message: 'We use cookies to remember your preferences',
            acceptText: 'Cool! On with the show!',
            autoEnable: false,
            fixed: true,
            zindex: '1',
        });
    });
</script>
</body>
</html>
