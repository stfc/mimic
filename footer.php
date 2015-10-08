<script>
    // Allows user to search
    $('#inLocate').keyup(function () {
        locateNode(this.value);
    });

    // Makes menu functional
    $('.tab').click(function () {
        $('.tab').removeClass('active');
        $(this).addClass('active');
        current_view = this.id;
        loading();
        getData(current_view);
    });

    // Shows and hides tabs
    $('.drop-head').click(function () {
        $(this).children('span').toggleClass('glyphicon-circle-arrow-right').toggleClass('glyphicon-circle-arrow-down');
    });

    // Navbar scroll
    $('.scroll').enscroll({
        propagateWheelEvent: false,
        verticalScrollerSide: 'left',
        easingDuration: 300,
        addPaddingToPane: false,
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
    <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
