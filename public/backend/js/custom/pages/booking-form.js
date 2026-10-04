// Selecting a customer fills the contact snapshot fields. They stay
// editable; a booking may carry a different contact than the CRM record.
(function () {
    var select = document.getElementById('bookingCustomer');
    if (!select) return;

    function fill() {
        var opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;
        var email = document.querySelector('[name="customer_email"]');
        var phone = document.querySelector('[name="customer_phone"]');
        if (email && !email.value) email.value = opt.dataset.email || '';
        if (phone && !phone.value) phone.value = opt.dataset.phone || '';
    }

    select.addEventListener('change', fill);
    // select2 fires its own event, not the native one.
    if (window.jQuery) jQuery(select).on('select2:select', fill);
})();
