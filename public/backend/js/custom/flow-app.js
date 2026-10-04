/* ============================================================
   FLOW App — consolidated page behaviours
   Extracted from inline <script> blocks across backend blades.
   Depends on jQuery, ApexCharts, SweetAlert2.
   ============================================================ */
(function () {
    'use strict';

    /* -------------------------------------------------------
       ApexCharts — reads #flow-data (application/json)
       and builds each chart using per-target templates.
       ------------------------------------------------------- */
    function initCharts() {
        if (typeof ApexCharts === 'undefined') return;

        var el = document.getElementById('flow-data');
        if (!el) return;

        var data;
        try { data = JSON.parse(el.textContent); } catch (e) { return; }
        if (!data || !data.charts) return;

        data.charts.forEach(function (item) {
            var target = document.querySelector(item.target);
            if (!target) return;

            var config = null;

            /* ---- per-target chart templates ---- */
            if (item.target === '#revenueChart') {
                config = {
                    chart: { height: 330, type: 'area', fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
                    series: item.series,
                    colors: ['#6366f1', '#06b6d4'],
                    stroke: { curve: 'smooth', width: [3, 2.5] },
                    fill: { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: .4, opacityTo: .05, stops: [0, 90] } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: item.categories, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: [
                        { labels: { formatter: function (v) { return v >= 1000 ? '\u09F3' + (v / 1000).toFixed(0) + 'k' : '\u09F3' + v; } } },
                        { opposite: true, labels: { formatter: function (v) { return Math.round(v); } } }
                    ],
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    legend: { show: false },
                    tooltip: { y: { formatter: function (v, o) { return o.seriesIndex === 0 ? '\u09F3' + v.toLocaleString() : v + ' bookings'; } } }
                };

            } else if (item.target === '#typeChart') {
                config = {
                    chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                    colors: ['#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'],
                    labels: item.labels,
                    series: item.series,
                    stroke: { width: 0 },
                    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', fontWeight: 700 } } } } },
                    legend: { position: 'bottom', markers: { radius: 12 } },
                    dataLabels: { enabled: false }
                };

            } else if (item.target === '#accChart') {
                config = {
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#10b981', '#ef4444'],
                    plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    series: item.series,
                    xaxis: { categories: item.categories }
                };

            } else if (item.target === '#accDonut') {
                config = {
                    chart: { type: 'donut', height: 320 },
                    colors: ['#4f46e5', '#06b6d4', '#f59e0b', '#ec4899', '#10b981'],
                    labels: item.labels,
                    series: item.series,
                    legend: { position: 'bottom' }
                };

            } else if (item.target === '#flRep') {
                config = {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    colors: ['#4f46e5'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                    dataLabels: { enabled: false },
                    series: item.series,
                    xaxis: { categories: item.categories }
                };

            } else if (item.target === '#rsFin') {
                config = {
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    colors: ['#10b981', '#ef4444'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    series: item.series,
                    xaxis: { categories: item.categories },
                    legend: { position: 'top' }
                };

            } else if (item.target === '#visaChart') {
                config = {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    colors: ['#4f46e5'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                    dataLabels: { enabled: false },
                    series: item.series,
                    xaxis: { categories: item.categories }
                };

            } else if (item.target === '#visaDonut') {
                config = {
                    chart: { type: 'donut', height: 300 },
                    colors: ['#10b981', '#f59e0b', '#ef4444'],
                    labels: item.labels,
                    series: item.series,
                    legend: { position: 'bottom' }
                };

            } else if (item.target === '#vrChart') {
                config = {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    colors: ['#4f46e5', '#10b981'],
                    stroke: { curve: 'smooth', width: 3 },
                    series: item.series,
                    xaxis: { categories: item.categories }
                };

            } else if (item.target === '#vrDonut') {
                config = {
                    chart: { type: 'pie', height: 300 },
                    colors: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b'],
                    labels: item.labels,
                    series: item.series,
                    legend: { position: 'bottom' }
                };

            } else if (item.target === '#agRep') {
                config = {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    colors: ['#4f46e5'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                    series: item.series,
                    xaxis: { categories: item.categories }
                };

            } else if (item.target === '#saasAna') {
                config = {
                    chart:{type:'bar',height:300,toolbar:{show:false}},
                    colors:['#4f46e5'],
                    plotOptions:{bar:{borderRadius:6,columnWidth:'40%'}},
                    dataLabels:{enabled:true},
                    series: item.series,
                    xaxis:{categories: item.categories}
                };
            }

            if (config) new ApexCharts(target, config).render();
        });
    }

    /* -------------------------------------------------------
       ToDo button handler
       ------------------------------------------------------- */
    function initTodoBtn() {
        var btn = document.getElementById('todo_btn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            var input = document.querySelector('.modal_todo_id');
            if (input) input.value = id;
        });
    }

    /* -------------------------------------------------------
       Boot
       ------------------------------------------------------- */
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initCharts();
        initTodoBtn();
    });
})();
