(function () {
    const customer = document.getElementById('customer_id');
    const lead = document.getElementById('lead_id');
    const name = document.getElementById('customer_name');

    if (!customer || !lead || !name) {
        return;
    }

    function selectedText(select) {
        const option = select.options[select.selectedIndex];
        return option && option.value ? option.text.trim() : '';
    }

    function syncName() {
        name.value = selectedText(customer) || selectedText(lead) || name.value;
    }

    customer.addEventListener('change', syncName);
    lead.addEventListener('change', syncName);
    syncName();
})();
