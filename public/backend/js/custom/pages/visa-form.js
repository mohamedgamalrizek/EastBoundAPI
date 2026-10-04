(function () {
    const customer  = document.getElementById('customer_id');
    const applicant = document.getElementById('applicant_name');
    const status    = document.getElementById('status');
    const expiry    = document.getElementById('expiry_date');
    const expiryBox = document.getElementById('expiry_date_group');

    // Most applications are the customer applying for themselves, so the
    // name follows the customer until somebody types a different one.
    if (customer && applicant) {
        customer.addEventListener('change', function () {
            if (applicant.value.trim() === '') {
                const option = customer.options[customer.selectedIndex];
                applicant.value = option && option.value ? option.textContent.trim() : '';
            }
        });
    }

    // An expiry date only means something on an issued visa.
    function syncExpiry() {
        if (!status || !expiry || !expiryBox) return;

        const approved = status.value === 'Approved';
        expiry.disabled = !approved;
        expiryBox.style.opacity = approved ? '1' : '.55';

        if (!approved) {
            expiry.value = '';
        }
    }

    if (status) {
        syncExpiry();
        status.addEventListener('change', syncExpiry);
        // The theme swaps these selects for select2, which fires jQuery events only.
        if (window.jQuery) { jQuery(status).on('change', syncExpiry); }
    }
})();
