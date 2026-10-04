"use strict";

async function tryDelete(event) {
    event.preventDefault();

    const element = event.currentTarget;

    const action = await Swal.fire({
        title: element.dataset.title ?? element.textContent,
        text: element.dataset.text ?? "This action cannot be reversed.",
        confirmButtonText: element.dataset.confirmButtonText ?? "Delete",
        confirmButtonColor: "#FF0303",
        icon: "warning",
        showCancelButton: true,
        cancelButtonText: element.dataset.cancelButtonText ?? "Cancel",
    });

    if (action.isConfirmed) {
        $.ajax({
            url: element.getAttribute("href") || element.dataset.url,
            type: "DELETE",
            dataType: "json",
            data: { _method: "DELETE" },
        })
            .done(function (response) {
                const icon = response.status ? "success" : "error";
                const message = response.message || "Something went wrong.";

                swalAlert(message, icon);

                const removeElement = $(`#${element.dataset.removeId}`);
                if (response.status && removeElement) {
                    removeElement.fadeOut(2000, () => removeElement.remove());
                }

                if (element.dataset.reload == true) {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .fail(function (error) {
                console.error("Error:", error);
                Swal.fire("Error", "Failed. Please try again later.", "error");
            });
    }
}

/**
 * Difference between using a button and an anchor tag for delete action confirmation:
 *
 * <!-- Using an anchor tag -->
 * <a href="/delete-item" onclick="tryDelete(event)"
 *    data-title="Are you sure you want to delete this item?"
 *    data-text="This action cannot be reversed."
 *    data-confirm-button-text="Delete"
 *    data-cancel-button-text="Cancel"
 *    data-remove-id="item123"
 *    data-reload="true">
 *    Delete
 * </a>
 *
 * <!-- Using a button element -->
 * <button onclick="tryDelete(event)" data-url="/delete-item" Delete </button>
 *
 * Note: If the element is not a tag, then 'data-url' attribute is needed.
 */
