"use strict";

async function submitForm(event) {
    event.preventDefault();

    const form = event.target; // Accessing the form from the event
    const url = form.getAttribute("action"); // get action url
    const formData = new FormData(form); // access form data

    const errorBoxes = document.querySelectorAll("[data-error-for]"); // access all error boxes
    errorBoxes.forEach((elm) => elm.classList.add("d-none")); // hide all error boxes

    const submitBtn = form.querySelector('button[type="submit"]'); // Target submit button
    const buttonText = submitBtn.innerHTML; // Get default button text

    const iconElement = document.createElement("i"); // Create i tag for fa icon

    iconElement.className = "fa fa-spinner fa-spin"; // Add fa icon class to i tag
    submitBtn.textContent = submitBtn.dataset.loadingText || "Processing" + " "; // Change the submit button text
    submitBtn.appendChild(iconElement); // Append the icon to the button
    submitBtn.disabled = true; // Disable the submit button

    try {
        const response = await fetch(url, {
            method: "POST",
            body: formData,
            headers: { Accept: "application/json" },
        });

        const data = await response.json();

        if (response.ok && data.status) {
            form.dataset.formReset ? form.reset() : "";

            const redirectTo = form.dataset.redirectUrl;
            if (redirectTo) {
                window.location.href = redirectTo;
            }

            if ($(".modal").length) {
                $(".modal").modal("hide");
            }
        }

        if (typeof swalAlert === "function" && data.message && !data.errors) {
            const icon = data.status ? "success" : "error";
            swalAlert(data.message, icon);
        }

        if (data.errors) {
            Object.entries(data.errors).forEach(([key, value]) => {
                let errorElm = form.querySelector(`[data-error-for="${key}"]`);

                if (errorElm) {
                    errorElm.textContent = value;
                    errorElm.classList.remove("d-none");
                } else {
                    if (typeof swalAlert === "function") {
                        swalAlert(data.message, "error");
                    }
                }

                let element = form.querySelector(`[name="${key}"]`);
                if (element) {
                    element.addEventListener("change", function () {
                        errorElm ? errorElm.classList.add("d-none") : "";
                    });
                }
            });
            return;
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = buttonText;
    }
}

/**
 * HTML form example:
 *
 * <form onsubmit="submitForm(event)" data-form-reset="true" data-redirect-url="/success" id="myForm">
 *     <div>
 *         <label for="title">Title</label>
 *         <input type="text" id="title" name="title">
 *         <small class="text-danger error-text d-none mt-2" data-error-for="title"></small>
 *     </div>
 *     <button type="submit" data-loading-text="Submitting...">Submit</button>
 * </form>
 *
 */
