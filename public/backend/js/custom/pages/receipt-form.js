// Picking an invoice fills in its customer and the amount still owed, so
// the common case (settling an invoice in full) is one click.
(function () {
    var invoice  = document.getElementById('invoice_id');
    var amount   = document.getElementById('amount');
    var customer = document.getElementById('customer_id');
    var name     = document.getElementById('customer_name');

    if (!invoice) return;

    invoice.addEventListener('change', function () {
        var option = invoice.options[invoice.selectedIndex];
        if (!option || !option.value) return;

        if (amount && !amount.value) amount.value = option.dataset.due || '';
        if (name && !name.value)     name.value   = option.dataset.customerName || '';
        if (customer && option.dataset.customerId) {
            customer.value = option.dataset.customerId;
            if (window.jQuery) jQuery(customer).trigger('change.select2');
        }
    });
})();
