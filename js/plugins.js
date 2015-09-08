function toggleRollup(c_name) {
    // Check whether user has consented to cookie usage
    if (Cookies.get('cb-enabled') === "accepted") {
        if (Cookies.get("rollup_" + c_name) === "hidden") {
		    $(c_name).show("blind");
            Cookies.get("rollup_" + c_name, "shown");
        } else {
		    $(c_name).hide("blind");
            Cookies.get("rollup_" + c_name, "hidden");
        }
    } else {
        $(c_name).toggle("blind");
    }
}
