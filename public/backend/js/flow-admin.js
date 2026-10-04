/* ============================================================
   FLOW Admin — behaviour layer
   Loaded after the template JS. Pure vanilla, no jQuery dep.

   Feature: collapsible list filters.
   Every list page renders a filter card (a GET <form> with a
   "Filter" submit button) directly above the data/list card.
   This hides that filter card by default and injects a "Filters"
   toggle button beside the list card's "Add" button — so the
   filter form is shown on demand instead of always taking space.
   Auto-detected from existing markup; no per-page changes needed.
   ============================================================ */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function initSidebar() {
        var wrapper = document.getElementById('main-wrapper');
        var sidebar = document.getElementById('backendSidebar');
        var menu = document.getElementById('backendSidebarMenu');
        var toggle = document.getElementById('backendSidebarToggle');
        var backdrop = document.getElementById('backendSidebarBackdrop');

        if (!wrapper || !sidebar || !menu || !toggle) return;

        var mobileQuery = window.matchMedia('(max-width: 991.98px)');

        function isMobile() {
            return mobileQuery.matches;
        }

        function directSubmenu(item) {
            return Array.prototype.find.call(item.children, function (child) {
                return child.tagName === 'UL';
            });
        }

        function setSubmenu(item, open) {
            var trigger = Array.prototype.find.call(item.children, function (child) {
                return child.tagName === 'A' && child.classList.contains('has-arrow');
            });
            var submenu = directSubmenu(item);

            if (!trigger || !submenu) return;

            item.classList.toggle('tv-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            submenu.setAttribute('aria-hidden', open ? 'false' : 'true');

            if (open) {
                submenu.style.setProperty('--tv-submenu-height', submenu.scrollHeight + 'px');
            }
        }

        function markCurrentLink() {
            var current = new URL(window.location.href);
            var currentPath = current.pathname.replace(/\/+$/, '') || '/';

            menu.querySelectorAll('a[href]').forEach(function (link) {
                if (link.classList.contains('has-arrow')) return;

                var target;
                try {
                    target = new URL(link.href, window.location.origin);
                } catch (e) {
                    return;
                }

                var targetPath = target.pathname.replace(/\/+$/, '') || '/';
                if (target.origin !== current.origin || targetPath !== currentPath) return;

                link.classList.add('mm-active');
                var item = link.closest('li');
                if (item) item.classList.add('mm-active');

                while (item) {
                    var parentMenu = item.parentElement;
                    if (!parentMenu || parentMenu === menu) break;
                    item = parentMenu.closest('li');
                    if (item) item.classList.add('mm-active');
                }
            });
        }

        function updateToggle() {
            var expanded = isMobile()
                ? wrapper.classList.contains('tv-sidebar-open')
                : !wrapper.classList.contains('tv-sidebar-collapsed');

            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.setAttribute('aria-label', expanded ? 'Close sidebar' : 'Open sidebar');
            toggle.classList.toggle('is-active', !expanded);
        }

        function setMobileOpen(open) {
            wrapper.classList.toggle('tv-sidebar-open', open);
            document.body.classList.toggle('tv-sidebar-lock', open);
            sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
            sidebar.inert = !open;
            updateToggle();
        }

        function syncResponsiveState() {
            if (isMobile()) {
                wrapper.classList.remove('tv-sidebar-collapsed');
                setMobileOpen(false);
            } else {
                wrapper.classList.remove('tv-sidebar-open');
                document.body.classList.remove('tv-sidebar-lock');
                sidebar.removeAttribute('aria-hidden');
                sidebar.inert = false;
                updateToggle();
            }
        }

        markCurrentLink();

        menu.querySelectorAll('a.has-arrow').forEach(function (trigger) {
            var item = trigger.parentElement;
            var submenu = item ? directSubmenu(item) : null;
            if (!item || !submenu) return;

            setSubmenu(item, item.classList.contains('mm-active') || !!submenu.querySelector('.mm-active'));

            trigger.addEventListener('click', function (event) {
                event.preventDefault();

                if (!isMobile() && wrapper.classList.contains('tv-sidebar-collapsed')) {
                    wrapper.classList.remove('tv-sidebar-collapsed');
                    updateToggle();
                }

                var willOpen = !item.classList.contains('tv-open');
                Array.prototype.forEach.call(item.parentElement.children, function (sibling) {
                    if (sibling !== item && sibling.classList && sibling.classList.contains('tv-open')) {
                        setSubmenu(sibling, false);
                    }
                });
                setSubmenu(item, willOpen);
            });
        });

        menu.querySelectorAll(':scope > li > a').forEach(function (link) {
            var label = link.querySelector('.nav-text');
            if (label) link.setAttribute('title', label.textContent.trim());
        });

        menu.querySelectorAll('a:not(.has-arrow)').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) setMobileOpen(false);
            });
        });

        toggle.addEventListener('click', function () {
            if (isMobile()) {
                setMobileOpen(!wrapper.classList.contains('tv-sidebar-open'));
                return;
            }

            wrapper.classList.toggle('tv-sidebar-collapsed');
            updateToggle();
        });

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                setMobileOpen(false);
                toggle.focus();
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && isMobile() && wrapper.classList.contains('tv-sidebar-open')) {
                setMobileOpen(false);
                toggle.focus();
            }
        });

        window.addEventListener('resize', function () {
            menu.querySelectorAll('li.tv-open').forEach(function (item) {
                var submenu = directSubmenu(item);
                if (submenu) submenu.style.setProperty('--tv-submenu-height', submenu.scrollHeight + 'px');
            });
        }, { passive: true });

        if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', syncResponsiveState);
        else mobileQuery.addListener(syncResponsiveState);

        syncResponsiveState();
    }

    function initLegacyAdminControls() {
        var checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('td input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                });
            });
        }

        document.querySelectorAll('[data-action]').forEach(function (control) {
            control.addEventListener('click', function (event) {
                var action = control.getAttribute('data-action');
                var card = control.closest('.card');
                if (!card) return;

                event.preventDefault();

                if (action === 'collapse') {
                    var body = card.querySelector(':scope > .card-body');
                    if (body) body.hidden = !body.hidden;
                    var icon = control.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('mdi-arrow-down');
                        icon.classList.toggle('mdi-arrow-up');
                    }
                } else if (action === 'expand') {
                    card.classList.toggle('card-fullscreen');
                    var expandIcon = control.querySelector('i');
                    if (expandIcon) {
                        expandIcon.classList.toggle('icon-size-actual');
                        expandIcon.classList.toggle('icon-size-fullscreen');
                    }
                } else if (action === 'close') {
                    card.hidden = true;
                } else if (action === 'reload') {
                    card.classList.add('card-load');
                    var loader = document.createElement('div');
                    loader.className = 'card-loader';
                    loader.innerHTML = '<i class="ti-reload rotate-refresh"></i>';
                    card.appendChild(loader);
                    window.setTimeout(function () {
                        loader.remove();
                        card.classList.remove('card-load');
                    }, 2000);
                }
            });
        });

        document.querySelectorAll('.btn-number').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                var field = button.getAttribute('data-field');
                var input = field && document.querySelector('input[name="' + CSS.escape(field) + '"]');
                if (!input) return;

                var value = parseInt(input.value, 10);
                if (Number.isNaN(value)) value = 0;
                input.value = button.getAttribute('data-type') === 'minus' ? value - 1 : value + 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        var rtlToggle = document.querySelector('.rtl-mode');
        if (rtlToggle) {
            rtlToggle.addEventListener('click', function () {
                document.body.dir = document.body.dir === 'rtl' ? 'ltr' : 'rtl';
            });
        }
    }

    function isFilterForm(form) {
        var method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method !== 'get') return false;
        // signal: contains a "filter" submit button (fa-filter icon)
        return !!form.querySelector('.fa-filter, [class*="filter"]');
    }

    function hasAppliedValue(form) {
        var fields = form.querySelectorAll(
            'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]), select, textarea'
        );
        return Array.prototype.some.call(fields, function (el) {
            return el.value && String(el.value).trim() !== '';
        });
    }

    function nextCard(el) {
        var n = el.nextElementSibling;
        while (n && !n.classList.contains('card')) n = n.nextElementSibling;
        return n;
    }

    function ensureResponsiveTables() {
        var tables = document.querySelectorAll('.content-body table.table, .dashboard-content table.table');

        Array.prototype.forEach.call(tables, function (table) {
            if (table.closest('.table-responsive')) return;
            if (table.closest('.dataTables_wrapper')) return;
            if (table.classList.contains('dt-table')) return;
            if (table.classList.contains('js-datatable')) return;

            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive tv-table-responsive';

            var parent = table.parentNode;
            if (!parent) return;

            parent.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    }

    ready(function () {
        initSidebar();
        initLegacyAdminControls();
        ensureResponsiveTables();

        var forms = document.querySelectorAll('.content-body form, .dashboard-content form');

        Array.prototype.forEach.call(forms, function (form) {
            if (!isFilterForm(form)) return;

            var filterCard = form.closest('.card');
            if (!filterCard || filterCard.dataset.tvFilter) return;
            filterCard.dataset.tvFilter = '1';
            filterCard.classList.add('tv-filter-card');

            var applied = hasAppliedValue(form);

            // Host the toggle in the list card header, next to "Add".
            var listCard = nextCard(filterCard);
            var header = listCard && listCard.querySelector('.card-header');

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'j-td-btn tv-filter-toggle';
            btn.setAttribute('aria-expanded', applied ? 'true' : 'false');
            btn.innerHTML = '<i class="fa fa-filter"></i> <span>Filters</span>';
            if (applied) btn.classList.add('is-active');

            if (!applied) filterCard.classList.add('tv-collapsed');

            btn.addEventListener('click', function () {
                var collapsed = filterCard.classList.toggle('tv-collapsed');
                btn.classList.toggle('is-active', !collapsed);
                btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                if (!collapsed) {
                    var first = filterCard.querySelector('input, select, textarea');
                    if (first) first.focus();
                }
            });

            // Preferred home: the page-header (breadcrumb) row, so the toggle
            // sits on the SAME row as the breadcrumb (right-aligned) instead of
            // dropping onto its own row below it.
            var pageHeader = document.querySelector('.page-header');

            if (pageHeader) {
                pageHeader.classList.add('tv-header-actions');
                pageHeader.appendChild(btn);
            } else if (header) {
                header.classList.add('tv-header-actions');
                var addBtn = header.querySelector('a.j-td-btn, a.btn, .j-td-btn');
                header.insertBefore(btn, addBtn || null);
            } else {
                var bar = document.createElement('div');
                bar.className = 'tv-filter-bar';
                bar.appendChild(btn);
                filterCard.parentNode.insertBefore(bar, filterCard);
            }
        });

        // --- Auto icons on action buttons -------------------------------
        // Picks a Font Awesome icon by the button's text and prepends it.
        // The FA kit's MutationObserver renders dynamically-added <i> tags,
        // so no markup changes are needed across the app.
        addButtonIcons();
    });

    function iconForText(t) {
        t = (t || '').trim().toLowerCase();
        if (!t) return null;
        if (/cancel|close|dismiss/.test(t)) return 'fa-xmark';
        if (/\bback\b|return/.test(t)) return 'fa-arrow-left';
        if (/clear|reset/.test(t)) return 'fa-eraser';
        if (/save|update|confirm|submit|apply|done/.test(t)) return 'fa-check';
        if (/\badd\b|create|new\b/.test(t)) return 'fa-plus';
        if (/delete|remove|trash/.test(t)) return 'fa-trash';
        if (/filter/.test(t)) return 'fa-filter';
        if (/search/.test(t)) return 'fa-magnifying-glass';
        if (/browse|upload|choose|file|attach/.test(t)) return 'fa-upload';
        if (/export|download/.test(t)) return 'fa-download';
        if (/print/.test(t)) return 'fa-print';
        if (/send|mail/.test(t)) return 'fa-paper-plane';
        if (/sign\s?in|log\s?in/.test(t)) return 'fa-right-to-bracket';
        if (/sign\s?up|register/.test(t)) return 'fa-user-plus';
        if (/edit/.test(t)) return 'fa-pen';
        if (/view|details|show/.test(t)) return 'fa-eye';
        if (/pay/.test(t)) return 'fa-credit-card';
        return null; // unknown → leave it without an icon
    }

    function addButtonIcons() {
        var sel = '.j-td-btn, .btn-blue, button[type="submit"], label.btn, .file-upload-btn';
        var els = document.querySelectorAll(sel);
        Array.prototype.forEach.call(els, function (el) {
            if (el.dataset.tvIco) return;
            // skip if it already shows an icon (i / svg / img)
            if (el.querySelector('i, svg, img')) { el.dataset.tvIco = '1'; return; }
            if (el.classList.contains('tv-filter-toggle')) return;
            var icon = iconForText(el.textContent);
            if (!icon) return;
            el.dataset.tvIco = '1';
            var i = document.createElement('i');
            i.className = 'fa ' + icon + ' tv-btn-ico';
            el.insertBefore(i, el.firstChild);
        });
    }
})();
