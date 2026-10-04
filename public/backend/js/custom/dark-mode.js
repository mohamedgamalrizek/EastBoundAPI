(function($) {
    "use strict"

    var STORAGE_KEY = 'flow-backend-theme';
    var body = $('body');
    var toggleBtn = $('#darkModeToggle');

    var sunSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></g></svg>';
    var moonSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 5h4m-2-2v4m.985 5.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>';

    function setTheme(theme) {
        if (theme === 'dark') {
            body.addClass('dark-mode');
            body.attr('data-theme', 'dark');
            body.attr('data-theme-version', 'dark');
            toggleBtn.html(sunSvg);
        } else {
            body.removeClass('dark-mode');
            body.removeAttr('data-theme');
            body.attr('data-theme-version', 'light');
            toggleBtn.html(moonSvg);
        }
        try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}
    }

    // Set initial icon based on current body state
    if (body.hasClass('dark-mode')) {
        toggleBtn.html(sunSvg);
    } else {
        toggleBtn.html(moonSvg);
    }

    toggleBtn.on('click', function(e) {
        e.preventDefault();
        setTheme(body.hasClass('dark-mode') ? 'light' : 'dark');
    });

})(jQuery);
