(function () {
    const list = document.getElementById('itineraryRows');
    const addBtn = document.getElementById('addItineraryDay');
    if (!list || !addBtn) return;

    const rowHtml = (index, day) => `
        <div class="form-row itinerary-row align-items-start border rounded p-2 mb-2">
            <div class="form-group col-md-1">
                <label>Day</label>
                <input type="number" min="1" name="itinerary[${index}][day_number]" class="form-control input-style-1" value="${day}">
            </div>
            <div class="form-group col-md-4">
                <label>Title</label>
                <input type="text" name="itinerary[${index}][title]" class="form-control input-style-1" placeholder="Arrival &amp; check-in">
            </div>
            <div class="form-group col-md-6">
                <label>Description</label>
                <textarea name="itinerary[${index}][description]" rows="2" class="form-control input-style-1" placeholder="What happens on this day."></textarea>
            </div>
            <div class="form-group col-md-1 d-flex align-items-end">
                <button type="button" class="j-td-btn btn-red remove-itinerary-row" title="Remove">&times;</button>
            </div>
        </div>`;

    // Names are rewritten after every change so removing a middle row cannot
    // leave a gap in the submitted array.
    const reindex = () => {
        list.querySelectorAll('.itinerary-row').forEach((row, i) => {
            row.querySelectorAll('[name^="itinerary["]').forEach(field => {
                field.name = field.name.replace(/itinerary\[\d+\]/, `itinerary[${i}]`);
            });
        });
    };

    addBtn.addEventListener('click', () => {
        const count = list.querySelectorAll('.itinerary-row').length;
        list.insertAdjacentHTML('beforeend', rowHtml(count, count + 1));
        reindex();
    });

    list.addEventListener('click', (e) => {
        if (e.target.closest('.remove-itinerary-row')) {
            e.target.closest('.itinerary-row').remove();
            reindex();
        }
    });
})();
