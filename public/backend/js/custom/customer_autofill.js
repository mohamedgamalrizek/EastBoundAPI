"use strict";

/**
 * Shared by any form with a #customer_id (linked Customer Account) select next
 * to a #customer_name text field — e.g. Transport Bookings, Package Bookings.
 * Selecting a customer account fills in the name field, so linking an account
 * has a visible, immediate effect instead of looking like an inert duplicate
 * of the name field.
 */
$(function () {
    var $customerSelect = $("#customer_id");
    var $customerName = $("#customer_name");

    if (!$customerSelect.length || !$customerName.length) {
        return;
    }

    $customerSelect.on("change", function () {
        var name = $(this).find("option:selected").data("name");
        if (name) {
            $customerName.val(name);
        }
    });
});
