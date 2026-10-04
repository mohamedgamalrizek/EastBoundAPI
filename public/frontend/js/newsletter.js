(function () {
    var form = document.querySelector('[data-newsletter-form]');
    if (!form || !window.fetch) return;

    var message = document.querySelector('[data-newsletter-message]');
    var submit = form.querySelector('[type="submit"]');
    var email = form.querySelector('[name="email"]');
    var defaultText = submit ? submit.textContent : 'Subscribe';

    function showMessage(text, type) {
        if (!message) return;
        message.textContent = text || '';
        message.classList.remove('d-none', 'text-success-tv', 'text-danger');
        message.classList.add(type === 'success' ? 'text-success-tv' : 'text-danger');
    }

    function setLoading(isLoading) {
        if (!submit) return;
        submit.disabled = isLoading;
        submit.textContent = isLoading ? (form.dataset.loadingText || 'Subscribing...') : defaultText;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        setLoading(true);
        if (email) email.classList.remove('is-invalid');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    var errors = result.payload && result.payload.errors ? result.payload.errors : {};
                    var firstError = errors.email && errors.email.length ? errors.email[0] : (form.dataset.genericError || 'Please enter a valid email address.');
                    if (email) email.classList.add('is-invalid');
                    showMessage(firstError, 'error');
                    return;
                }

                showMessage(result.payload.message || (form.dataset.successText || 'Thanks! You are subscribed to FLOW deals.'), 'success');
                form.reset();
            })
            .catch(function () {
                showMessage(form.dataset.failText || 'Subscription failed. Please try again.', 'error');
            })
            .finally(function () {
                setLoading(false);
            });
    });
}());
