(function () {
    'use strict';
    var field = document.getElementById('nana_entry_type');
    if (!field) return;
    function update() {
        var event = field.value === 'nana_timeline';
        document.querySelectorAll('[data-nana-character-fields]').forEach(function (el) { el.hidden = event; });
        document.querySelectorAll('[data-nana-event-fields]').forEach(function (el) { el.hidden = !event; });
        document.querySelectorAll('[data-nana-character-label]').forEach(function (el) {
            el.textContent = event ? el.dataset.nanaTimelineLabel : el.dataset.nanaCharacterLabel;
        });
    }
    field.addEventListener('change', update);
    update();
})();
