(function () {
    var booking = document.getElementById('booking_id');
    var passenger = document.getElementById('passenger_name');
    var customerId = document.getElementById('customer_id');

    function selectedOption(select) {
        return select && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
    }

    function onBookingChange() {
        var opt = selectedOption(booking);
        if (!opt || !opt.value) {
            if (customerId) customerId.value = '';
            return;
        }

        if (customerId) {
            customerId.value = opt.dataset.customerId || '';
        }

        if (passenger && !passenger.value && opt.dataset.customerName) {
            passenger.value = opt.dataset.customerName;
        }
    }

    // From/To pickers compose the stored route string (codes when both
    // are known, city names otherwise) — same route the website builds.
    var from = document.getElementById('route_from');
    var to = document.getElementById('route_to');
    var route = document.getElementById('route');

    function cityCode(select) {
        var opt = selectedOption(select);
        return opt ? (opt.dataset.code || opt.value || '') : '';
    }

    function composeRoute() {
        var f = cityCode(from);
        var t = cityCode(to);
        var parts = [];
        if (f) parts.push(f); else if (from && from.value) parts.push(from.value);
        if (t) parts.push(t); else if (to && to.value) parts.push(to.value);
        route.value = parts.join(' -> ');
    }

    if (from) from.addEventListener('change', composeRoute);
    if (to) to.addEventListener('change', composeRoute);
    if (window.jQuery) {
        jQuery(from).on('select2:select', composeRoute);
        jQuery(to).on('select2:select', composeRoute);
    }

    if (booking) {
        booking.addEventListener('change', onBookingChange);
        if (window.jQuery) jQuery(booking).on('select2:select', onBookingChange);
    }
})();
