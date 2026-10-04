"use strict";

$(document).ready(function () {
    driverSettings();

    $("#mail_driver").change(driverSettings);
});

// Shows only the fields the selected driver actually uses. Every driver shares
// the From address / name and the signature, so those stay visible throughout.
function driverSettings() {
    var mail_driver = $("#mail_driver").val();

    $(".smtp, .sendmail, .gmail_api").hide();

    if (mail_driver === "smtp") {
        $(".smtp").show();
    } else if (mail_driver === "sendmail") {
        $(".sendmail").show();
    } else if (mail_driver === "gmail_api") {
        $(".gmail_api").show();
    }

    $(".mail-common").show();
}
