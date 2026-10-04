"use strict";

$("#search").on("input", function () {
    $.ajax({
        type: "post",
        url: $(this).data("url"),
        data: { search: $(this).val() },
        dataType: "json",
        success: function (response) {
            console.log(response);

            let options = "";
            for (let route of response) {
                options += `<option>${route.title}</option>`;
            }
            $("#route_list").html(options);
        },
    });
});

$("#search").on("change", function () {
    // Submit the form when an option is selected from the datalist
    $(this).closest("form").submit();
});

// window.scrollTo({ top: 900, behavior: 'smooth' })
