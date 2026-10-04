"use strict";

// Lightweight client-side DataTables initializer.
// Any table tagged with `.js-datatable` gets search / sort / pagination.
// Tag the action column header with `data-orderable="false"` to keep it static.
$(document).ready(function () {
    function normalizeDataTableLayout($table) {
        if (!$table || !$table.length) return;

        var $wrapper = $table.closest(".dataTables_wrapper");
        if (!$wrapper.length) return;

        var $rows = $wrapper.children("div");
        var $topRow = $rows.filter(function () {
            var $row = $(this);
            return $row.find(".dataTables_length, .dataTables_filter").length > 0;
        }).first();
        var $bottomRow = $rows.filter(function () {
            var $row = $(this);
            return $row.find(".dataTables_info, .dataTables_paginate").length > 0;
        }).first();

        function normalizeRow($row, rowClass, leftClass, rightClass) {
            if (!$row.length) return;

            var $children = $row.children("div");
            var $left = $children.eq(0);
            var $right = $children.eq(1);

            $row.removeClass(function (_, className) {
                return (className || "")
                    .split(/\s+/)
                    .filter(function (name) {
                        return name === "row" || /^col-/.test(name) || /^tv-dt-/.test(name);
                    })
                    .join(" ");
            });

            $row.addClass(rowClass);

            [$left, $right].forEach(function ($col) {
                if (!$col || !$col.length) return;

                $col.removeClass(function (_, className) {
                    return (className || "")
                        .split(/\s+/)
                        .filter(function (name) {
                            return /^col-/.test(name) || /^tv-dt-/.test(name);
                        })
                        .join(" ");
                });
            });

            if ($left.length) $left.addClass(leftClass);
            if ($right.length) $right.addClass(rightClass);
        }

        normalizeRow(
            $topRow,
            "tv-dt-toolbar d-flex justify-content-between align-items-center flex-wrap",
            "tv-dt-toolbar__start d-flex align-items-center flex-wrap",
            "tv-dt-toolbar__end d-flex align-items-center flex-wrap justify-content-md-end"
        );

        normalizeRow(
            $bottomRow,
            "tv-dt-footer d-flex justify-content-between align-items-center flex-wrap pt-4 mt-4",
            "tv-dt-footer__start d-flex align-items-center flex-wrap",
            "tv-dt-footer__end d-flex align-items-center flex-wrap justify-content-md-end"
        );
    }

    function wrapDataTable($table) {
        if (!$table || !$table.length) return;

        if ($table.parent().hasClass("table-responsive")) {
            $table.parent().addClass("tv-table-shell dt-table-shell");
            normalizeDataTableLayout($table);
            return;
        }

        $table.wrap('<div class="table-responsive tv-table-shell dt-table-shell"></div>');
        normalizeDataTableLayout($table);
    }

    $(".js-datatable").each(function () {
        var $table = $(this);
        var noSort = [];
        $table
            .find("thead th")
            .each(function (index) {
                if ($(this).data("orderable") === false) {
                    noSort.push({ orderable: false, targets: index });
                }
            });

        $table.DataTable({
            order: [],
            columnDefs: noSort,
            language: {
                search: "",
                searchPlaceholder: "Search...",
            },
            initComplete: function () {
                wrapDataTable($table);
            },
        });
    });
});
