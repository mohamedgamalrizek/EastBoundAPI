$(document).ready(function () {
    var routes = window.notificationRoutes || {};
    var $badge = $("#notificationUnreadBadge");
    var $list = $("#notificationList");

    function refreshUnreadCount() {
        if (!routes.unreadCount) return;
        $.getJSON(routes.unreadCount, function (data) {
            var unread = data.unread || 0;
            // Visibility is a class toggle, not an inline style, so the badge
            // keeps its positioning rules from inline-utilities.css.
            if (unread > 0) {
                $badge.text(unread > 99 ? "99+" : unread).removeClass("d-none");
            } else {
                $badge.addClass("d-none");
            }
        });
    }

    function loadDropdown() {
        if (!routes.dropdown) return;
        $.ajax({ url: routes.dropdown, type: "get", dataType: "html" })
            .done(function (data) {
                $list.html(data);
            })
            .fail(function () {
                $list.html(
                    '<li class="media dropdown-item text-center text-muted">Something went wrong.</li>'
                );
            });
    }

    refreshUnreadCount();
    setInterval(refreshUnreadCount, 60000);

    $(".notification_dropdown").on("show.bs.dropdown", loadDropdown);

    // Dropdown rows: mark read in place, no reload — a small, ephemeral list.
    $(document).on("click", "#notificationList .js-mark-read", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        if (!id || !routes.markRead) return;

        $.ajax({ url: routes.markRead + id + "/read", type: "POST", dataType: "json" }).done(
            function () {
                refreshUnreadCount();
            }
        );

        $(this).closest("li").removeClass("font-weight-bold");
    });

    // Full "all notifications" page: reload to reflect the new state everywhere.
    $(document).on("click", ".js-mark-read-page", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        if (!id || !routes.markRead) return;

        $.ajax({ url: routes.markRead + id + "/read", type: "POST", dataType: "json" }).done(
            function () {
                location.reload();
            }
        );
    });

    $("#markAllReadLink").on("click", function (e) {
        e.preventDefault();
        if (!routes.markAllRead) return;

        $.ajax({ url: routes.markAllRead, type: "POST", dataType: "json" }).done(function () {
            refreshUnreadCount();
            loadDropdown();
        });
    });

    $("#markAllReadBtn").on("click", function () {
        if (!routes.markAllRead) return;

        $.ajax({ url: routes.markAllRead, type: "POST", dataType: "json" }).done(function () {
            location.reload();
        });
    });
});
