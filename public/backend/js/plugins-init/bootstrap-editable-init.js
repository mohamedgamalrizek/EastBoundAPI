/**
 * X-editable demo initialisation.
 *
 * The showcase page carries two copies of the same set of fields: one in
 * popup mode (default) and one in inline mode, prefixed with "inline-".
 * Both copies are wired below with the same options.
 */

$(function () {

    // Colour used for each value of the "sex" select. The empty key is the
    // "not selected" state.
    var SEX_COLOURS = {
        "": "#98a6ad",
        1: "#5fbeaa",
        2: "#5d9cec"
    };

    var SEX_SOURCE = [
        { value: 1, text: "Male" },
        { value: 2, text: "Female" }
    ];

    /**
     * Renders the "sex" field: shows the label for the selected value and
     * tints it, or empties the element when nothing matches.
     *
     * Called by x-editable with `this` bound to the element, so it has to
     * stay a normal function.
     */
    function displaySex(value, sourceData) {
        var selected = $.grep(sourceData, function (item) {
            return item.value == value;
        });

        if (selected.length) {
            $(this).text(selected[0].text).css("color", SEX_COLOURS[value]);
        } else {
            $(this).empty();
        }
    }

    /** Rejects an empty (or whitespace-only) value. */
    function requireValue(value) {
        if ($.trim(value) == "") {
            return "This field is required";
        }
    }

    // --- Popup mode ---------------------------------------------------------

    $("#username").editable({
        type: "text",
        pk: 1,
        name: "username",
        title: "Enter username"
    });

    $("#firstname").editable({
        validate: requireValue
    });

    $("#sex").editable({
        prepend: "not selected",
        source: SEX_SOURCE,
        display: displaySex
    });

    $("#status").editable();

    $("#group").editable({
        showbuttons: false
    });

    $("#dob").editable();

    $("#comments").editable({
        showbuttons: "bottom"
    });

    // --- Inline mode --------------------------------------------------------

    $("#inline-username").editable({
        type: "text",
        pk: 1,
        name: "username",
        title: "Enter username",
        mode: "inline"
    });

    $("#inline-firstname").editable({
        validate: requireValue,
        mode: "inline"
    });

    $("#inline-sex").editable({
        prepend: "not selected",
        mode: "inline",
        source: SEX_SOURCE,
        display: displaySex
    });

    $("#inline-status").editable({
        mode: "inline"
    });

    $("#inline-group").editable({
        showbuttons: false,
        mode: "inline"
    });

    $("#inline-dob").editable({
        mode: "inline"
    });

    $("#inline-comments").editable({
        showbuttons: "bottom",
        mode: "inline"
    });

});
