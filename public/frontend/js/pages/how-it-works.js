document.addEventListener('click', function (e) {
    var btn = e.target.closest('.hiw-toggle');
    if (!btn) return;
    var panel = document.querySelector(btn.getAttribute('data-hiw-target'));
    if (!panel) return;
    var open = panel.classList.toggle('open');
    btn.classList.toggle('open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
});
