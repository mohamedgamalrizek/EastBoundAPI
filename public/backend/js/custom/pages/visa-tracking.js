// The bar, the percentage and the badge follow the dropdowns as they are
// changed. Before this the card only caught up after Update reloaded the
// page, which read as "the status does not affect the progress bar".
//
// These steps mirror VisaApplication::progressFor() and statusTone(); if
// one side changes, change the other.
(function () {
    function progressFor(status, documents) {
        if (status === 'Approved' || status === 'Rejected') return 100;
        if (status === 'In Review') return 75;
        if (documents === 'Verified') return 60;
        if (documents === 'Submitted') return 40;
        return 15;
    }

    function toneFor(status) {
        if (status === 'Approved') return 'success';
        if (status === 'Rejected') return 'danger';
        if (status === 'In Review') return 'info';
        return 'warning';
    }

    document.querySelectorAll('.js-visa-card').forEach(function (card) {
        const status    = card.querySelector('.js-visa-status');
        const documents = card.querySelector('.js-visa-documents');

        // A decided case has no form to react to.
        if (!status || !documents) return;

        const bar     = card.querySelector('.js-visa-bar');
        const percent = card.querySelector('.js-visa-percent');
        const badge   = card.querySelector('.js-visa-badge');
        const unsaved = card.querySelector('.js-visa-unsaved');

        const savedStatus    = status.value;
        const savedDocuments = documents.value;

        function preview() {
            const value = progressFor(status.value, documents.value);
            const tone  = toneFor(status.value);

            percent.textContent = value;
            bar.style.width     = value + '%';
            bar.className       = 'progress-bar bg-' + tone + ' js-visa-bar';
            badge.textContent   = status.value;
            badge.className     = 'bullet-badge bullet-badge-' + tone + ' js-visa-badge';

            const dirty = status.value !== savedStatus || documents.value !== savedDocuments;
            unsaved.classList.toggle('d-none', !dirty);
        }

        status.addEventListener('change', preview);
        documents.addEventListener('change', preview);
    });
})();
