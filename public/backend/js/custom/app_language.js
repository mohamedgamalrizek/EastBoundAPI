"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const module = document.querySelector("#module");
    // module.addEventListener("change", loadPhrase);
    $(module).on("change", loadPhrase);

    loadPhrase();
});

async function loadPhrase() {
    const code = document.querySelector("#code").value;

    const data = { code: code, module: module.value };
    const params = new URLSearchParams(data).toString();
    const url = `${module.dataset.url}?${params}`;

    try {
        const response = await fetch(url, {
            method: "GET",
            headers: { Accept: "application/json" },
        });

        const data = await response.json();

        if (!response.ok) {
            data.message ? swalAlert(data.message, "error") : "";
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const tableBody = document.querySelector("#phrases-table tbody");
        tableBody.innerHTML = "";

        Object.entries(data.phrases).forEach(([key, value], index) => {
            const newRow = tableBody.insertRow();

            newRow.insertCell().textContent = index + 1;
            newRow.insertCell().textContent = transformTerm(key);

            // Built as nodes, not as an HTML string. A translation is free text
            // and plenty of them legitimately contain a double quote — Hebrew
            // writes email as דוא"ל, and the frontend strings carry markup like
            // <span class="text-gradient">. Interpolating those into
            // value="..." closed the attribute early and the phrase came back
            // truncated the moment the form was saved. Assigning .value never
            // re-parses, so any character survives a round trip.
            const field = newRow.insertCell();
            field.className = "w-75";

            const input = document.createElement("input");
            input.type = "text";
            input.className = "form-control";
            input.name = `phrases[${key}]`;
            input.value = value ?? "";
            field.appendChild(input);
        });
    } catch (error) {
        console.error("Error:", error);
    }
}

function transformTerm(term) {
    term = term.toLowerCase(); // Convert to lowercase
    term = term.replace(/[_-]/g, " "); // Replace underscores and hyphens with spaces
    // term = term.replace(/\b\w/g, (char) => char.toUpperCase()); // Capitalize the first letter of each word
    term = term.replace(/^./, (char) => char.toUpperCase()); // Capitalize the first character of the string
    return term;
}
