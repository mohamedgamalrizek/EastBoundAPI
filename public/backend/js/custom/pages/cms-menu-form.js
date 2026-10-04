/* Position and Parent are two halves of one choice: a child always lives in
   its parent's region, so the parent list only ever offers parents from the
   selected region.

   The options are rebuilt rather than just disabled — select2 keeps its own
   copy of the list, so toggling `disabled`/`hidden` on the underlying
   <option> elements did not reliably survive a re-open. Removing them is
   unambiguous: select2 cannot show what is not in the DOM. */
(function () {
    function boot() {
        var position = document.getElementById('position');
        var parent   = document.getElementById('parent_id');
        if (!position || !parent || !window.jQuery) return;

        var $position = $(position);
        var $parent   = $(parent);

        // Snapshot every parent before any filtering happens.
        var all = $parent.find('option').map(function () {
            return this.value
                ? { value: this.value, label: this.textContent.trim(), position: this.dataset.position }
                : null;
        }).get().filter(Boolean);

        var topLevelLabel = $parent.find('option[value=""]').first().text() || '— Top level —';
        var preselected   = $parent.val();

        function renderParents(region, keep) {
            var wanted = all.filter(function (o) { return o.position === region; });

            $parent.empty().append(new Option(topLevelLabel, ''));
            wanted.forEach(function (o) {
                var opt = new Option(o.label, o.value, false, o.value === keep);
                opt.dataset.position = o.position;
                $parent.append(opt);
            });

            // Keep the selection only when it still belongs to this region.
            $parent.val(wanted.some(function (o) { return o.value === keep; }) ? keep : '');
            $parent.trigger('change.select2');
        }

        function onPositionChange() {
            renderParents($position.val(), $parent.val());
        }

        function onParentChange() {
            var value = $parent.val();
            if (!value) {
                $position.prop('disabled', false).trigger('change.select2');
                return;
            }
            // The server inherits the parent's region anyway; show it locked
            // so the two fields can never disagree.
            var chosen = all.filter(function (o) { return o.value === value; })[0];
            if (chosen) {
                $position.val(chosen.position).prop('disabled', true).trigger('change.select2');
            }
        }

        $position.on('change', onPositionChange);
        $parent.on('change', onParentChange);

        renderParents($position.val(), preselected);
        onParentChange();

        // A disabled select is not submitted — re-enable on the way out.
        $(position.form).on('submit', function () { $position.prop('disabled', false); });
    }

    // Runs after _developer.js has initialised select2 on these fields.
    if (window.jQuery) $(function () { setTimeout(boot, 0); });
})();
