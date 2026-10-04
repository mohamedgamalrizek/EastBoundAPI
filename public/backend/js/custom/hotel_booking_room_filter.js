"use strict";

/**
 * Hotel Booking form: filters the Room dropdown down to only the rooms that
 * belong to the currently selected hotel, so a booking can't be created with
 * a room from a different hotel.
 */
$(function () {
    var $hotel = $("#hotel_id");
    var $room = $("#hotel_room_id");

    if (!$hotel.length || !$room.length) {
        return;
    }

    var allRoomOptions = $room.find("option").clone();

    function filterRoomsByHotel() {
        var hotelId = $hotel.val();
        var selected = $room.val();

        $room.empty();
        allRoomOptions.each(function () {
            var $opt = $(this);
            if (!$opt.val() || !hotelId || String($opt.data("hotel-id")) === String(hotelId)) {
                $room.append($opt.clone());
            }
        });

        $room.val($room.find('option[value="' + selected + '"]').length ? selected : "");
        $room.trigger("change");
    }

    $hotel.on("change", filterRoomsByHotel);
    filterRoomsByHotel();
});
