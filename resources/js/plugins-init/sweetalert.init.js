/**
 * SweetAlert demo initialisation.
 *
 * Wires each button on the "Sweet Alert" showcase page to one example dialog.
 * Every handler is independent — nothing here is shared state — so a button
 * can be removed from the markup by deleting its block below.
 */

document.querySelector(".sweet-wrong").onclick = function () {
    sweetAlert("Oops...", "Something went wrong !!", "error");
};

document.querySelector(".sweet-message").onclick = function () {
    swal("Hey, Here's a message !!");
};

document.querySelector(".sweet-text").onclick = function () {
    swal("Hey, Here's a message !!", "It's pretty, isn't it?");
};

document.querySelector(".sweet-success").onclick = function () {
    swal("Hey, Good job !!", "You clicked the button !!", "success");
};

document.querySelector(".sweet-confirm").onclick = function () {
    swal({
        title: "Are you sure to delete ?",
        text: "You will not be able to recover this imaginary file !!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !!",
        closeOnConfirm: false
    }, function () {
        swal("Deleted !!", "Hey, your imaginary file has been deleted !!", "success");
    });
};

document.querySelector(".sweet-success-cancel").onclick = function () {
    swal({
        title: "Are you sure to delete ?",
        text: "You will not be able to recover this imaginary file !!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !!",
        cancelButtonText: "No, cancel it !!",
        closeOnConfirm: false,
        closeOnCancel: false
    }, function (confirmed) {
        if (confirmed) {
            swal("Deleted !!", "Hey, your imaginary file has been deleted !!", "success");
        } else {
            swal("Cancelled !!", "Hey, your imaginary file is safe !!", "error");
        }
    });
};

document.querySelector(".sweet-image-message").onclick = function () {
    swal({
        title: "Sweet !!",
        text: "Hey, Here's a custom image !!",
        imageUrl: "../assets/images/hand.jpg"
    });
};

document.querySelector(".sweet-html").onclick = function () {
    swal({
        title: "Sweet !!",
        text: "<span style='color:#ff0000'>Hey, you are using HTML !!<span>",
        html: true
    });
};

document.querySelector(".sweet-auto").onclick = function () {
    swal({
        title: "Sweet auto close alert !!",
        text: "Hey, i will close in 2 seconds !!",
        timer: 2000,
        showConfirmButton: false
    });
};

document.querySelector(".sweet-prompt").onclick = function () {
    swal({
        title: "Enter an input !!",
        text: "Write something interesting !!",
        type: "input",
        showCancelButton: true,
        closeOnConfirm: false,
        animation: "slide-from-top",
        inputPlaceholder: "Write something"
    }, function (value) {
        // `false` means the user dismissed the prompt — leave the dialog alone.
        if (value === false) {
            return false;
        }

        if (value === "") {
            swal.showInputError("You need to write something!");
            return false;
        }

        swal("Hey !!", "You wrote: " + value, "success");
    });
};

document.querySelector(".sweet-ajax").onclick = function () {
    swal({
        title: "Sweet ajax request !!",
        text: "Submit to run ajax request !!",
        type: "info",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function () {
        // Stands in for a real request; swap the timeout for your own AJAX call.
        setTimeout(function () {
            swal("Hey, your ajax request finished !!");
        }, 2000);
    });
};
