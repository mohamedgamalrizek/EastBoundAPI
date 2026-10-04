/* ============================================================
   FLOW Inline — consolidated extracted inline JavaScript
   Extracted from <script> blocks across backend blade files.
   Depends on jQuery, SweetAlert2, ApexCharts, flatpickr, select2.
   Loaded via backend/partials/footer.blade.php.
   ============================================================ */
(function () {
    'use strict';

    /* ========================================================
       Auth page form elements (from auth/master.blade.php)
       select2 & flatpickr init for auth pages that don't load
       the backend layouts.
       ======================================================== */
    function initAuthFormElements() {
        // Only run on auth pages (body has .auth-body class)
        if (!document.querySelector('.auth-body')) return;

        $(document).ready(function () {
            $('.select2').select2({
                tags: true,
                placeholder: 'Select an option',
                allowClear: true,
            });

            $('.flatpickr').flatpickr({
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d',
            });

            $('.flatpickr-range').flatpickr({
                mode: 'range',
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d',
            });
        });
    }

    /* ========================================================
       Demo login (from auth/partials/demo-logins.blade.php)
       ======================================================== */
    window.flowDemoLogin = function (email) {
        var form = document.querySelector('form[data-demo]');
        if (!form) return;
        form.querySelector('input[name="email"]').value = email;
        form.querySelector('input[name="password"]').value = '12345678';
        form.submit();
    };

    /* ========================================================
       Resend token (from auth/password/token_form.blade.php
       and auth/register/register_token_form.blade.php)

       The trigger element must have:
         data-user-id="{{ session('user_id') }}"
         data-csrf-token="{{ csrf_token() }}"
       ======================================================== */
    window.resendToken = function (event) {
        event.preventDefault();
        var btn = event.target;
        var parentP = btn.closest('p');
        var originalHtml = parentP.innerHTML;
        parentP.innerHTML = 'Sending ... <i class="fa fa-spinner fa-spin"></i>';
        var uid = btn.getAttribute('data-user-id');
        var token = btn.getAttribute('data-csrf-token');
        $.ajax({
            type: 'POST',
            url: btn.href || btn.getAttribute('data-url'),
            data: { _token: token, user_id: uid },
            dataType: 'json',
            success: function (response) {
                parentP.innerHTML = response.message;
                parentP.classList.add('text-success');
            },
            error: function () {
                parentP.innerHTML = 'Error occurred while resending token.';
            },
            complete: function () {
                setTimeout(function () {
                    parentP.innerHTML = originalHtml;
                    parentP.classList.remove('text-success');
                }, 5000);
            },
        });
    };

    /* ========================================================
       delete_row (from backend/partials/delete-ajax.blade.php)
       Legacy function — used by legacy inline delete links.
       ======================================================== */
    window.delete_row = function (route, row_id, reload) {
        console.log(reload);
        var table_row = '#row_' + row_id;
        var url = '/' + route + '/' + row_id;
        console.log(url);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
        }).then(function (confirmed) {
            if (confirmed.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    dataType: 'json',
                    data: { id: row_id, _method: 'DELETE' },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                    url: url,
                })
                    .done(function (response) {
                        Swal.fire(response[2], response[0], response[1]);
                        $(table_row).fadeOut(2000);
                        if (reload) {
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .fail(function (error) {
                        console.log(error);
                        Swal.fire(
                            'opps...',
                            'something_went_wrong_with_ajax',
                            'error'
                        );
                    });
            }
        });
    };

    /* ========================================================
       DataTable init (from resources/views/components/data-table.blade.php)

       Reads data-dt-page-length and data-dt-order from
       .dt-table elements.  The order string must be valid JSON
       (e.g. '[[0,"desc"]]').
       ======================================================== */
    function initDataTables() {
        if (typeof $.fn.DataTable === 'undefined') return;

        function normalizeDataTableLayout($table) {
            if (!$table || !$table.length) return;

            var $wrapper = $table.closest('.dataTables_wrapper');
            if (!$wrapper.length) return;

            var $rows = $wrapper.children('div');
            var $topRow = $rows.filter(function () {
                var $row = $(this);
                return $row.find('.dataTables_length, .dataTables_filter').length > 0;
            }).first();
            var $bottomRow = $rows.filter(function () {
                var $row = $(this);
                return $row.find('.dataTables_info, .dataTables_paginate').length > 0;
            }).first();

            function normalizeRow($row, rowClass, leftClass, rightClass) {
                if (!$row.length) return;

                var $children = $row.children('div');
                var $left = $children.eq(0);
                var $right = $children.eq(1);

                $row.removeClass(function (_, className) {
                    return (className || '')
                        .split(/\s+/)
                        .filter(function (name) {
                            return name === 'row' || /^col-/.test(name) || /^tv-dt-/.test(name);
                        })
                        .join(' ');
                });

                $row.addClass(rowClass);

                [$left, $right].forEach(function ($col) {
                    if (!$col || !$col.length) return;

                    $col.removeClass(function (_, className) {
                        return (className || '')
                            .split(/\s+/)
                            .filter(function (name) {
                                return /^col-/.test(name) || /^tv-dt-/.test(name);
                            })
                            .join(' ');
                    });
                });

                if ($left.length) $left.addClass(leftClass);
                if ($right.length) $right.addClass(rightClass);
            }

            normalizeRow(
                $topRow,
                'tv-dt-toolbar d-flex justify-content-between align-items-center flex-wrap pt-0 mt-0',
                'tv-dt-toolbar__start d-flex align-items-center flex-wrap pt-0 mt-0',
                'tv-dt-toolbar__end d-flex align-items-center flex-wrap justify-content-md-end pt-0 mt-0'
            );

            normalizeRow(
                $bottomRow,
                'tv-dt-footer d-flex justify-content-between align-items-center flex-wrap pt-4 mt-4',
                'tv-dt-footer__start d-flex align-items-center flex-wrap',
                'tv-dt-footer__end d-flex align-items-center flex-wrap justify-content-md-end'
            );
        }

        function wrapDataTable($table) {
            if (!$table || !$table.length) return;

            var $wrapper = $table.closest('.dataTables_wrapper');
            if (!$wrapper.length) return;

            if ($table.parent().hasClass('table-responsive')) {
                $table.parent().addClass('tv-table-shell dt-table-shell');
                normalizeDataTableLayout($table);
                return;
            }

            $table.wrap('<div class="table-responsive tv-table-shell dt-table-shell"></div>');
            normalizeDataTableLayout($table);
        }

        $('.dt-table').each(function () {
            var $t = $(this);
            // Skip already-initialised tables
            if ($t.hasClass('dt-init')) return;
            $t.addClass('dt-init');

            // An empty list's Blade markup renders one "No data" <tr> with a
            // single <td colspan="N"> spanning every header, rather than N
            // separate cells. DataTables sources its initial data straight
            // from this DOM, so on a truly empty table it finds a row with
            // 1 cell where every header expects N and throws "Incorrect
            // column count" (datatables.net/tn/18). Strip that row instead
            // and let DataTables' own emptyTable message (below) show —
            // that option exists for exactly this case.
            var headerCount = $t.find('thead th').length;
            if (headerCount) {
                $t.find('tbody tr').each(function () {
                    if ($(this).children('td').length < headerCount) $(this).remove();
                });
            }

            var pageLength = parseInt($t.data('dt-page-length'), 10) || 10;
            var order = [];

            try {
                var raw = $t.data('dt-order');
                if (raw) order = JSON.parse(raw);
            } catch (e) {
                order = [];
            }

            $t.DataTable({
                pageLength: pageLength,
                lengthMenu: [10, 25, 50, 100],
                order: order,
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found.',
                },
                initComplete: function () {
                    wrapDataTable($t);
                    buildColumnFilters($t, this.api());
                },
            });
        });
    }

    /* ========================================================
       Per-column list filters

       Every .dt-table gets a "Filter" toggle next to its search
       box. It opens a collapsed panel with one field per column:
       a dropdown for enum-like columns (2–25 distinct values) and
       a contains-search text box for everything else, applied via
       DataTables' client-side column search. Action and image
       columns are skipped automatically.
       ======================================================== */
    function buildColumnFilters($table, dt) {
        var $wrapper = $table.closest('.dataTables_wrapper');
        if (!$wrapper.length || $wrapper.data('tvFiltersBuilt')) return;
        $wrapper.data('tvFiltersBuilt', true);

        var SKIP_HEADERS = /^(action|actions|image|photo|logo|avatar|icon|preview|#)?$/i;
        var MAX_OPTIONS = 25;
        var fields = [];

        dt.columns().every(function (idx) {
            var header = ($(this.header()).text() || '').trim();
            if (SKIP_HEADERS.test(header)) return;

            var seen = {};
            var values = [];
            var rows = 0;

            this.nodes().to$().each(function () {
                rows++;
                var v = $(this).text().trim().replace(/\s+/g, ' ');
                if (v && v !== '—' && !seen[v]) {
                    seen[v] = true;
                    values.push(v);
                }
            });

            if (values.length < 2) return; // empty or constant column

            // Enum-like → dropdown; anything else (names, dates, amounts) → text box
            var isEnum = values.length <= MAX_OPTIONS && !(rows > 6 && values.length === rows);

            if (isEnum) {
                values.sort(function (a, b) {
                    return a.localeCompare(b, undefined, { numeric: true });
                });
                fields.push({ idx: idx, label: header, type: 'select', values: values });
            } else {
                fields.push({ idx: idx, label: header, type: 'text' });
            }
        });

        if (!fields.length) return;

        // Toggle button, next to the DataTables search box
        var $toggle = $(
            '<button type="button" class="tv-filter-toggle" aria-expanded="false">' +
                '<i class="fa fa-filter"></i><span>Filter</span>' +
                '<span class="tv-filter-count" hidden></span>' +
            '</button>'
        );
        var $filterBox = $wrapper.find('.dataTables_filter').first();
        if ($filterBox.length) {
            $filterBox.prepend($toggle);
        } else {
            $wrapper.prepend($toggle);
        }

        // Collapsed panel with one field per filterable column. The theme
        // forces padding:0 on .tv-filter-card itself (cards pad via body),
        // so the grid lives inside a .tv-card-body like every other card.
        var $panel = $('<div class="tv-filter-card tv-filter-panel tv-collapsed"></div>');
        var $panelBody = $('<div class="tv-card-body"></div>').appendTo($panel);
        var $grid = $('<div class="tv-filter-grid"></div>').appendTo($panelBody);

        fields.forEach(function (f) {
            var $field = $('<div class="tv-filter-field"></div>');
            $('<label class="label-style-1"></label>').text(f.label).appendTo($field);

            if (f.type === 'select') {
                var $select = $('<select class="form-control"></select>').attr('data-col', f.idx);
                $('<option value="">All</option>').appendTo($select);
                f.values.forEach(function (v) {
                    $('<option></option>').attr('value', v).text(v).appendTo($select);
                });
                $select.appendTo($field);
            } else {
                $('<input type="text" class="form-control">')
                    .attr('data-col', f.idx)
                    .attr('placeholder', 'Search ' + f.label.toLowerCase() + '...')
                    .appendTo($field);
            }

            $field.appendTo($grid);
        });

        var $clear = $(
            '<button type="button" class="tv-filter-clear">' +
                '<i class="fa fa-times"></i> Clear filters' +
            '</button>'
        );
        var $clearWrap = $('<div class="tv-filter-field tv-filter-actions"></div>').append($clear).appendTo($grid);

        // Full-width row between the toolbar and the table
        var $topRow = $wrapper.children('div').filter(function () {
            return $(this).find('.dataTables_length, .dataTables_filter').length > 0;
        }).first();
        if ($topRow.length) {
            $topRow.after($panel);
        } else {
            $table.closest('.dt-table-shell, .table-responsive').before($panel);
        }

        function refreshState() {
            var active = 0;
            $grid.find('select, input').each(function () {
                if (($(this).val() || '').trim()) active++;
            });
            $toggle.toggleClass('is-active', active > 0);
            $toggle.find('.tv-filter-count')
                .text(active)
                .prop('hidden', active === 0);
            $clearWrap.toggle(active > 0);
        }

        $grid.on('change', 'select', function () {
            var val = $(this).val();
            var col = dt.column(parseInt($(this).attr('data-col'), 10));
            // Whitespace-tolerant exact match: cell markup often carries
            // newlines/indentation around the visible text.
            var rx = val
                ? '^\\s*' + $.fn.dataTable.util.escapeRegex(val).replace(/\s+/g, '\\s+') + '\\s*$'
                : '';
            col.search(rx, true, false).draw();
            refreshState();
        });

        var textDebounce;
        $grid.on('input', 'input[data-col]', function () {
            var $input = $(this);
            clearTimeout(textDebounce);
            textDebounce = setTimeout(function () {
                dt.column(parseInt($input.attr('data-col'), 10))
                    .search($input.val().trim())
                    .draw();
                refreshState();
            }, 250);
        });

        $clear.on('click', function () {
            $grid.find('select, input').val('');
            dt.columns().search('');
            dt.draw();
            refreshState();
        });

        $toggle.on('click', function () {
            var open = $panel.hasClass('tv-collapsed');
            $panel.toggleClass('tv-collapsed', !open);
            $toggle.attr('aria-expanded', open ? 'true' : 'false');
        });

        refreshState();
    }

    // Blade-authored filter cards (server-side filter forms) reuse the same
    // toggle: <button data-tv-filter-target="#cardId"> shows/hides the card.
    $(document).on('click', '[data-tv-filter-target]', function () {
        var $btn = $(this);
        var $card = $($btn.attr('data-tv-filter-target'));
        if (!$card.length) return;
        var collapsed = $card.toggleClass('tv-collapsed').hasClass('tv-collapsed');
        $btn.attr('aria-expanded', collapsed ? 'false' : 'true');
    });

    /* ========================================================
       List-analytics chart renderer
       (from resources/views/components/list-analytics.blade.php)

       Expects one or more <script type="application/json"
       data-la-charts> blocks injected by the Blade component.
       ======================================================== */
    function initListAnalyticsCharts() {
        if (typeof ApexCharts === 'undefined') return;

        var blocks = document.querySelectorAll(
            'script[type="application/json"][data-la-charts]'
        );
        if (!blocks.length) return;

        /* Categorical series colours, assigned in this fixed order and never
           cycled. The previous set failed a colour-blindness check — its green
           and amber sat ΔE 5.7 apart under protanopia, which is exactly the
           Gold/Silver pair in the customer donut. This order is validated:
           worst adjacent ΔE 9.1 (protan), 19.6 normal vision. */
        var palette = [
            '#2a78d6', // blue
            '#eb6834', // orange
            '#1baf7a', // aqua
            '#eda100', // yellow
            '#e87ba4', // magenta
            '#4a3aa7', // violet
        ];
        var INK = '#16233a',
            MUTED = '#78879c',
            GRID = '#e8edf4',
            SURFACE = '#ffffff';

        blocks.forEach(function (block) {
            var data;
            try {
                data = JSON.parse(block.textContent);
            } catch (e) {
                return;
            }
            if (!data) return;
            var uid = block.getAttribute('data-la-charts');

            if (data.donut) {
                var donutTarget = document.getElementById(uid + '_donut');
                if (donutTarget) {
                    var donutTotal = (data.donut.series || []).reduce(function (a, b) {
                        return a + (Number(b) || 0);
                    }, 0);

                    new ApexCharts(donutTarget, {
                        chart: {
                            type: 'donut',
                            height: 260,
                            fontFamily: 'Inter, sans-serif',
                        },
                        series: data.donut.series,
                        labels: data.donut.labels,
                        colors: palette,
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            markers: { radius: 12 },
                            itemMargin: { horizontal: 8, vertical: 2 },
                            labels: { colors: MUTED },
                        },
                        // The segment colours sit under 3:1 against white, so the
                        // count is written on the segment rather than left to hue.
                        dataLabels: {
                            enabled: true,
                            formatter: function (percent, opts) {
                                return opts.w.config.series[opts.seriesIndex];
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 700,
                                colors: ['#ffffff'],
                            },
                            dropShadow: {
                                enabled: true,
                                top: 1,
                                left: 0,
                                blur: 2,
                                opacity: 0.35,
                            },
                        },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                donut: {
                                    size: '72%',
                                    labels: {
                                        show: true,
                                        name: { fontSize: '12px', color: MUTED, offsetY: 18 },
                                        // Hero number in the hole — the total the
                                        // segments add up to.
                                        value: {
                                            fontSize: '26px',
                                            fontWeight: 800,
                                            color: INK,
                                            offsetY: -14,
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            color: MUTED,
                                            formatter: function () { return donutTotal; },
                                        },
                                    },
                                },
                            },
                        },
                        // 2px of surface between segments keeps adjacent fills apart.
                        stroke: { width: 2, colors: [SURFACE] },
                        tooltip: { fillSeriesColor: false },
                    }).render();
                }
            }

            if (data.trend) {
                var trendTarget = document.getElementById(uid + '_trend');
                if (trendTarget) {
                    var trendType = data.trend.type || 'area';
                    var isBar = trendType === 'bar';
                    var multi = (data.trend.series || []).length > 1;

                    new ApexCharts(trendTarget, {
                        chart: {
                            type: trendType,
                            height: 260,
                            toolbar: { show: false },
                            fontFamily: 'Inter, sans-serif',
                            parentHeightOffset: 0,
                        },
                        series: data.trend.series,
                        xaxis: {
                            categories: data.trend.labels,
                            labels: { style: { colors: MUTED, fontSize: '11px' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            tooltip: { enabled: false },
                        },
                        yaxis: {
                            min: 0,
                            forceNiceScale: true,
                            labels: {
                                style: { colors: MUTED, fontSize: '11px' },
                                formatter: function (v) { return Math.round(v); },
                            },
                        },
                        colors: palette,
                        dataLabels: { enabled: false },
                        // Thin marks with rounded data-ends; bars were rendering
                        // as hairlines pinned to the baseline.
                        plotOptions: isBar ? {
                            bar: {
                                columnWidth: '45%',
                                borderRadius: 4,
                                borderRadiusApplication: 'end',
                            },
                        } : {},
                        stroke: isBar
                            ? { show: true, width: 2, colors: [SURFACE] }
                            : { curve: 'smooth', width: 2 },
                        fill: isBar ? { type: 'solid', opacity: 1 } : {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.02,
                            },
                        },
                        grid: {
                            borderColor: GRID,
                            strokeDashArray: 4,
                            xaxis: { lines: { show: false } },
                            padding: { left: 4, right: 4, top: 0 },
                        },
                        // One series is named by the block's own title; a legend
                        // box would just repeat it.
                        legend: multi ? {
                            position: 'top',
                            horizontalAlign: 'right',
                            fontSize: '12px',
                            markers: { radius: 12 },
                            labels: { colors: MUTED },
                        } : { show: false },
                        tooltip: { shared: multi, intersect: false },
                    }).render();
                }
            }
        });
    }

    /* ========================================================
       Date fields
       --------------------------------------------------------
       Admin forms were a mix: a few used flatpickr, most were raw
       <input type="date"> rendering the browser's own grey picker,
       which looked nothing like the rest of the styled form.

       Every date input is upgraded to flatpickr here so no blade has
       to opt in. The underlying field keeps its name and its Y-m-d
       value, so nothing server-side changes — only the visible
       control (altInput) shows the friendlier format.
       ======================================================== */
    function initDatePickers(root) {
        if (typeof flatpickr === 'undefined') return;

        var scope = root || document;
        var fields = scope.querySelectorAll('input[type="date"], input.js-datepicker');

        Array.prototype.forEach.call(fields, function (input) {
            if (input._flatpickr || input.classList.contains('flatpickr')) return;

            // flatpickr behaves best on a text input; swapping the type also
            // stops the native picker from opening on top of the calendar.
            var min = input.getAttribute('min');
            var max = input.getAttribute('max');
            input.setAttribute('type', 'text');

            flatpickr(input, {
                dateFormat: 'Y-m-d',      // what gets submitted — unchanged
                altInput: true,           // what the user sees
                altFormat: 'd M Y',
                allowInput: true,
                minDate: min || null,
                maxDate: max || null,
                disableMobile: true,
                onReady: function (dates, str, fp) {
                    if (fp.altInput) {
                        fp.altInput.className = input.className + ' tv-datepicker';
                        if (input.placeholder) fp.altInput.placeholder = input.placeholder;
                    }
                },
            });
        });
    }

    // Fields inside ajax-loaded modals arrive after boot.
    if (window.jQuery) {
        $(document).on('shown.bs.modal', function (e) { initDatePickers(e.target); });
    }

    /* ========================================================
       Live totals

       A read-only field whose value is the sum of other fields,
       so staff see the customer-facing figure while they type:

         <input data-total-of="govt_fee,service_fee" readonly>
       ======================================================== */
    function initLiveTotals() {
        var totals = document.querySelectorAll('[data-total-of]');

        Array.prototype.forEach.call(totals, function (output) {
            var sources = output
                .getAttribute('data-total-of')
                .split(',')
                .map(function (id) { return document.getElementById(id.trim()); })
                .filter(Boolean);

            if (!sources.length) return;

            var prefix = output.getAttribute('data-total-prefix') || '';

            var render = function () {
                var sum = sources.reduce(function (carry, field) {
                    return carry + (parseFloat(field.value) || 0);
                }, 0);
                output.value = prefix + sum.toLocaleString('en-US', { maximumFractionDigits: 2 });
            };

            sources.forEach(function (field) { field.addEventListener('input', render); });
            render();
        });
    }

    /* ========================================================
       Select-driven autofill

       A field that copies a value off the chosen <option> when a
       select changes, leaving manual edits alone afterwards:

         <select id="visa_service_id">
           <option data-govt-fee="6000">…
         <input data-fill-from="visa_service_id" data-fill-key="govtFee">

       data-fill-key is the camelCase form of the option's data
       attribute (data-govt-fee → govtFee).
       ======================================================== */
    function initSelectAutofill() {
        var targets = document.querySelectorAll('[data-fill-from]');
        var bySource = {};

        Array.prototype.forEach.call(targets, function (field) {
            var sourceId = field.getAttribute('data-fill-from');
            (bySource[sourceId] = bySource[sourceId] || []).push(field);
        });

        Object.keys(bySource).forEach(function (sourceId) {
            var select = document.getElementById(sourceId);
            if (!select) return;

            var apply = function () {
                var option = select.options[select.selectedIndex];
                if (!option || !option.value) return;

                bySource[sourceId].forEach(function (field) {
                    var value = option.dataset[field.getAttribute('data-fill-key')];
                    if (value === undefined) return;
                    field.value = value;
                    // Let live totals and other listeners recompute.
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                });
            };

            // jQuery-driven select2 fires its change through jQuery only.
            if (window.jQuery) $(select).on('change', apply);
            else select.addEventListener('change', apply);
        });
    }

    /* ========================================================
       Boot
       ======================================================== */
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initAuthFormElements();
        initDataTables();
        initListAnalyticsCharts();
        initDatePickers();
        initLiveTotals();
        initSelectAutofill();
    });
})();
