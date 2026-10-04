"use strict";

document.addEventListener("DOMContentLoaded", () => {
    ajaxCsrf();
    sessionAlert();

    initSelect2();
    initFlatpickr();
    initSummernote();

    // updated version of file upload  for ot_fileUploader only
    $(".ot_fileUploader input[type=file]").change(fileUploadEffect);

    // for any other image upload effect
    const inputs = document.querySelectorAll(".image-upload");
    inputs.forEach((input) =>
        input.addEventListener("change", imageUploadEffect)
    );

    let deleteParents = document.querySelectorAll(".delete-parent");
    deleteParents.forEach((me) => {
        me.addEventListener("click", () => {
            const parentElement = me.closest(me.dataset.parent);
            parentElement ? parentElement.remove() : "";
        });
    });
});

function ajaxCsrf() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta.getAttribute("content");
    $.ajaxSetup({ headers: { "X-CSRF-TOKEN": csrfToken } });
}

function sessionAlert() {
    const elements = document.querySelectorAll(".session-alert");
    elements.forEach((element) => {
        const message = element.dataset.message;
        const icon = element.dataset.icon;
        swalAlert(message, icon);
        element.remove();
    });
}

function swalAlert(message = "message", icon = "error") {
    try {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener("mouseenter", Swal.stopTimer);
                toast.addEventListener("mouseleave", Swal.resumeTimer);
            },
        });

        if (Toast) {
            Toast.fire({ icon: icon, title: message });
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

function initSelect2() {
    // Placeholder mirrors the field's label (same text the sibling inputs
    // use), falling back to a generic prompt when no label is found.
    function select2Placeholder(el) {
        var label = el.id ? $("label[for='" + el.id + "']").first().text() : "";
        return (
            $(el).attr("placeholder") ||
            label.replace(/\*/g, "").trim() ||
            "Select an option"
        );
    }

    $(".select2").each(function () {
        $(this).select2({
            tags: $(this).data("tag") || false,
            maximumSelectionLength: $(this).data("max") || null,
            placeholder: select2Placeholder(this),
            allowClear: true,
        });
    });

    $(".search-select2").each(function () {
        const params = $(this).data("params");

        const config = {
            allowClear: true,
            placeholder: select2Placeholder(this),
            ajax: {
                url: $(this).data("url"),
                type: "get",
                dataType: "json",
                delay: 250,
                data: function (search) {
                    return { search: search.term, select2: true, ...params };
                },
                processResults: function (response) {
                    !response.status ? swalAlert(response.message) : "";
                    return { results: response.data };
                },
                cache: true,
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            },
        };

        config.tags = $(this).data("tag") || false;
        config.maximumSelectionLength = $(this).data("max") || null;

        $(this).select2(config);
    });
}

function initFlatpickr() {
    if (typeof flatpickr === "undefined") {
        return;
    }
    $(".flatpickr").flatpickr({
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
    });

    $(".flatpickr-range").flatpickr({
        mode: "range",
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
    });

    $('input[type="time"]').flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        altInput: true,
        altFormat: "h:i K",
    });
}

function initSummernote() {
    if (typeof $.fn.summernote !== "undefined") {
        $(".summernote").summernote({
            inheritPlaceholder: true,
            height: "auto",
            minHeight: 250,
            toolbar: [
                ["style", ["bold", "italic", "underline", "clear"]],
                ["font", ["fontname", "fontsize", "forecolor", "backcolor"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["table", ["table"]],
                ["insert", ["link", "picture"]],
                ["view", ["fullscreen"]],
            ],
        });

        $(".summernote-2").summernote({
            inheritPlaceholder: true,
            toolbar: [
                ["font", ["bold", "italic", "underline", "clear"]],
                ["style", ["fontname", "fontsize", "forecolor", "backcolor"]],
            ],
        });
    }
}

function addToSummernote(element, targetId = "description") {
    const body = document.querySelector(`#${targetId}`);
    $(body).summernote("code", (body.value += element.textContent));
}

// ot_fileUploader
function fileUploadEffect(event) {
    const element = event.currentTarget;
    var file = element.files[0];

    if (file) {
        const uploader = element.closest(".ot_fileUploader");
        uploader.querySelector(".placeholder").value = file.name;

        if (!file.type.startsWith("image/")) {
            let img = uploader.querySelector("img");
            if (img) {
                img.remove();
            }
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            let img = uploader.querySelector("img");

            if (!img) {
                img = document.createElement("img");
                uploader.insertBefore(img, uploader.firstChild);
            }

            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// image upload effect
function imageUploadEffect(event) {
    const element = event.currentTarget;
    const file = element.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.querySelector(element.dataset.target);
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

function deleteElement(event) {
    const element = event.currentTarget;
    const parentElement = element.closest(element.dataset.target);
    if (parentElement) parentElement.remove();
}
