"use strict";
$(document).ready(function () {
    // Keep a module's master checkbox in sync with its individual permissions.
    function syncModuleToggle($row) {
        var $keys = $row.find(".common-key");
        var $toggle = $row.find(".module-toggle");
        $toggle.prop("checked", $keys.length > 0 && $keys.filter(":checked").length === $keys.length);
    }

    // Master checkbox: check/uncheck every permission in the module row.
    $(document).on("change", ".module-toggle", function () {
        var checked = $(this).is(":checked");
        $(this).closest("tr").find(".common-key").prop("checked", checked);
    });

    // Permission dependency rules + master sync.
    $(document).on("change", ".common-key", function () {
        var $row = $(this).closest("tr");
        var value = $(this).val().split("_");

        if (value[1] === "read" || value[0] === "manage") {
            // Unchecking "read" clears the whole module — it gates the others.
            if (!$(this).is(":checked")) {
                $row.find(".common-key").prop("checked", false);
            }
        } else {
            // Any other permission implies "read" access.
            if ($(this).is(":checked")) {
                $row.find(".common-key").first().prop("checked", true);
            }
        }

        syncModuleToggle($row);
    });

    // Reflect the initial state on load (e.g. the edit screen).
    $(".permission-table tbody tr").each(function () {
        syncModuleToggle($(this));
    });
});
