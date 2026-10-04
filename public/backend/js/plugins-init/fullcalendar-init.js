/**
 * FullCalendar initialisation.
 *
 * Builds the calendar on #calendar and wires the three interactions the demo
 * page offers:
 *   - dragging an entry out of #external-events onto a day (drop)
 *   - selecting a range to create an event (select)
 *   - clicking an existing event to rename or delete it (eventClick)
 *
 * The instance is exposed as $.CalendarApp so a page can call
 * $.CalendarApp.init() again after replacing the markup.
 */

!function ($) {
    "use strict";

    var CalendarApp = function () {
        this.$body = $("body");
        this.$modal = $("#event-modal");
        this.$event = "#external-events div.external-event";
        this.$calendar = $("#calendar");
        this.$saveCategoryBtn = $(".save-category");
        this.$categoryForm = $("#add-category form");
        this.$extEvents = $("#external-events");
        this.$calendarObj = null;
    };

    /**
     * Handles an external event being dropped on the calendar.
     * `$draggedEl` is the element from #external-events, `date` the drop target.
     */
    CalendarApp.prototype.onDrop = function ($draggedEl, date) {
        var originalEventObject = $draggedEl.data("eventObject");
        var $categoryClass = $draggedEl.attr("data-class");

        // Copy so the source element keeps its own template object.
        var copiedEventObject = $.extend({}, originalEventObject);
        copiedEventObject.start = date;

        if ($categoryClass) {
            copiedEventObject.className = [$categoryClass];
        }

        this.$calendar.fullCalendar("renderEvent", copiedEventObject, true);

        // "Remove after drop" checkbox on the demo page.
        if ($("#drop-remove").is(":checked")) {
            $draggedEl.remove();
        }
    };

    /** Opens the modal for an existing event, offering rename and delete. */
    CalendarApp.prototype.onEventClick = function (calEvent, jsEvent, view) {
        var self = this;
        var form = $("<form></form>");

        form.append("<label>Change event name</label>");
        form.append("<div class='input-group'><input class='form-control' type=text value='" + calEvent.title + "' /><span class='input-group-btn'><button type='submit' class='btn btn-success waves-effect waves-light'><i class='fa fa-check'></i> Save</button></span></div>");

        self.$modal.modal({
            backdrop: "static"
        });

        self.$modal
            .find(".delete-event").show().end()
            .find(".save-event").hide().end()
            .find(".modal-body").empty().prepend(form).end()
            .find(".delete-event").unbind("click").on("click", function () {
                self.$calendarObj.fullCalendar("removeEvents", function (ev) {
                    return ev._id == calEvent._id;
                });
                self.$modal.modal("hide");
            });

        self.$modal.find("form").on("submit", function () {
            calEvent.title = form.find("input[type=text]").val();
            self.$calendarObj.fullCalendar("updateEvent", calEvent);
            self.$modal.modal("hide");
            return false;
        });
    };

    /** Opens the modal for a newly selected date range so a new event can be added. */
    CalendarApp.prototype.onSelect = function (start, end, allDay) {
        var self = this;

        self.$modal.modal({
            backdrop: "static"
        });

        var form = $("<form></form>");
        form.append("<div class='row'></div>");
        form.find(".row")
            .append("<div class='col-md-6'><div class='form-group'><label class='control-label'>Event Name</label><input class='form-control' placeholder='Insert Event Name' type='text' name='title'/></div></div>")
            .append("<div class='col-md-6'><div class='form-group'><label class='control-label'>Category</label><select class='form-control' name='category'></select></div></div>")
            .find("select[name='category']")
            .append("<option value='bg-danger'>Danger</option>")
            .append("<option value='bg-success'>Success</option>")
            .append("<option value='bg-dark'>Dark</option>")
            .append("<option value='bg-primary'>Primary</option>")
            .append("<option value='bg-pink'>Pink</option>")
            .append("<option value='bg-info'>Info</option>")
            .append("<option value='bg-warning'>Warning</option></div></div>");

        self.$modal
            .find(".delete-event").hide().end()
            .find(".save-event").show().end()
            .find(".modal-body").empty().prepend(form).end()
            .find(".save-event").unbind("click").on("click", function () {
                form.submit();
            });

        self.$modal.find("form").on("submit", function () {
            var title = form.find("input[name='title']").val();
            var beginning = form.find("input[name='beginning']").val();
            var ending = form.find("input[name='ending']").val();
            var categoryClass = form.find("select[name='category'] option:checked").val();

            if (title !== null && title.length != 0) {
                self.$calendarObj.fullCalendar("renderEvent", {
                    title: title,
                    start: start,
                    end: end,
                    allDay: false,
                    className: categoryClass
                }, true);
                self.$modal.modal("hide");
            } else {
                alert("You have to give a title to your event");
            }

            return false;
        });

        self.$calendarObj.fullCalendar("unselect");
    };

    /** Makes every entry in #external-events draggable onto the calendar. */
    CalendarApp.prototype.enableDrag = function () {
        $(this.$event).each(function () {
            // Store the event template on the element so onDrop can copy it.
            var eventObject = {
                title: $.trim($(this).text())
            };

            $(this).data("eventObject", eventObject);

            $(this).draggable({
                zIndex: 999,
                revert: true,          // snap back when not dropped on the calendar
                revertDuration: 0
            });
        });
    };

    CalendarApp.prototype.init = function () {
        this.enableDrag();

        // Demo events, positioned relative to today so the calendar is never empty.
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var now = new Date($.now());

        var defaultEvents = [{
            title: "Chicken Burger",
            start: new Date($.now() + 158000000),
            className: "bg-dark"
        }, {
            title: "Soft drinks",
            start: now,
            end: now,
            className: "bg-danger"
        }, {
            title: "Hot dog",
            start: new Date($.now() + 338000000),
            className: "bg-primary"
        }];

        var self = this;

        self.$calendarObj = self.$calendar.fullCalendar({
            slotDuration: "00:15:00",
            minTime: "08:00:00",
            maxTime: "19:00:00",
            defaultView: "month",
            handleWindowResize: true,
            height: $(window).height() - 100,
            header: {
                left: "prev,next today",
                center: "title",
                right: "month,agendaWeek,agendaDay"
            },
            events: defaultEvents,
            editable: true,
            droppable: true,
            eventLimit: true,
            selectable: true,
            drop: function (date) {
                self.onDrop($(this), date);
            },
            select: function (start, end, allDay) {
                self.onSelect(start, end, allDay);
            },
            eventClick: function (calEvent, jsEvent, view) {
                self.onEventClick(calEvent, jsEvent, view);
            }
        });

        // "Add category" form: appends a new draggable entry to #external-events.
        this.$saveCategoryBtn.on("click", function () {
            var categoryName = self.$categoryForm.find("input[name='category-name']").val();
            var categoryColor = self.$categoryForm.find("select[name='category-color']").val();

            if (categoryName !== null && categoryName.length != 0) {
                self.$extEvents.append('<div class="external-event bg-' + categoryColor + '" data-class="bg-' + categoryColor + '" style="position: relative;"><i class="fa fa-move"></i>' + categoryName + "</div>");
                self.enableDrag();
            }
        });
    };

    $.CalendarApp = new CalendarApp();
    $.CalendarApp.Constructor = CalendarApp;

}(window.jQuery);

!function ($) {
    "use strict";
    $.CalendarApp.init();
}(window.jQuery);
