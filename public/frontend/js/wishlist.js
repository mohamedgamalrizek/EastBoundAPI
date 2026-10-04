(function () {
    if (!window.fetch) return;

    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : null;

    function toast(text, isError) {
        var el = document.createElement('div');
        el.textContent = text;
        el.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);'
            + 'background:' + (isError ? '#EF4444' : '#1E293B') + ';color:#fff;padding:.6rem 1.1rem;'
            + 'border-radius:999px;font-size:.85rem;z-index:2000;box-shadow:0 8px 24px rgba(0,0,0,.18);'
            + 'opacity:0;transition:opacity .2s ease;';
        document.body.appendChild(el);
        requestAnimationFrame(function () { el.style.opacity = '1'; });
        setTimeout(function () {
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 250);
        }, 2200);
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-wishlist-toggle]');
        if (!btn) return;

        event.preventDefault();
        if (btn.disabled) return;

        var icon = btn.querySelector('i');
        btn.disabled = true;

        fetch(btn.dataset.toggleUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = btn.dataset.loginUrl;
                    return null;
                }
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            })
            .then(function (result) {
                if (!result) return;

                var errorText = btn.dataset.errorText || 'Something went wrong. Please try again.';

                if (!result.ok) {
                    toast((result.payload && result.payload.message) || errorText, true);
                    return;
                }

                var wishlisted = !!(result.payload.data && result.payload.data.wishlisted);
                var removeLabel = btn.dataset.labelRemove || 'Remove from wishlist';
                var saveLabel = btn.dataset.labelSave || 'Save to wishlist';
                btn.classList.toggle('is-active', wishlisted);
                btn.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
                btn.setAttribute('aria-label', wishlisted ? removeLabel : saveLabel);
                if (icon) {
                    icon.classList.toggle('fa-solid', wishlisted);
                    icon.classList.toggle('fa-regular', !wishlisted);
                }
                toast(result.payload.message);
            })
            .catch(function () {
                toast(btn.dataset.errorText || 'Something went wrong. Please try again.', true);
            })
            .finally(function () {
                btn.disabled = false;
            });
    });
}());
