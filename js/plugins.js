function toggleRollup(c_name) {
    // Check whether user has consented to cookie usage
    if (jQuery.cookie('cc_cookie_accept') == "cc_cookie_accept") {
        if ($.cookie("rollup_" + c_name) == "hidden") {
		    $(c_name).show("blind");
            $.cookie("rollup_" + c_name, "shown");
        } else {
		    $(c_name).hide("blind");
            $.cookie("rollup_" + c_name, "hidden");
        }
    }
}
