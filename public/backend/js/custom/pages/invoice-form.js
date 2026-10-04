(function () {
    var bookingSelect = document.getElementById('booking_id');
    var customerSelect = document.getElementById('customer_id');
    var billingName = document.getElementById('customer_name');
    var amount = document.getElementById('amount');

    function triggerSelect2(select) {
        if (window.jQuery && select) {
            jQuery(select).trigger('change.select2');
        }
    }

    function fillFromCustomer() {
        if (!customerSelect || !billingName) return;
        var option = customerSelect.options[customerSelect.selectedIndex];
        if (option && option.value) {
            billingName.value = option.dataset.name || option.text || billingName.value;
        }
    }

    function fillFromBooking() {
        if (!bookingSelect) return;
        var option = bookingSelect.options[bookingSelect.selectedIndex];
        if (!option || !option.value) return;

        if (customerSelect && option.dataset.customerId) {
            customerSelect.value = option.dataset.customerId;
            triggerSelect2(customerSelect);
        }
        if (billingName) {
            billingName.value = option.dataset.customerName || billingName.value;
        }
        if (amount && option.dataset.amount) {
            amount.value = option.dataset.amount;
        }
    }

    if (bookingSelect) {
        bookingSelect.addEventListener('change', fillFromBooking);
        if (window.jQuery) jQuery(bookingSelect).on('select2:select', fillFromBooking);
    }

    if (customerSelect) {
        customerSelect.addEventListener('change', fillFromCustomer);
        if (window.jQuery) jQuery(customerSelect).on('select2:select', fillFromCustomer);
    }
})();
