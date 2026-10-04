(function () {
    var select = document.getElementById('contactSubject');
    var custom = document.getElementById('customSubjectWrap');
    if (!select || !custom) return;

    function toggleCustomSubject() {
        custom.classList.toggle('d-none', select.value !== 'Other');
    }

    select.addEventListener('change', toggleCustomSubject);
    toggleCustomSubject();
})();
