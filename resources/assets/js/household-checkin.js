document.querySelectorAll('[data-household-checkin]').forEach(function (form) {
    function updateSummary() {
        const arriving = form.querySelectorAll('input[name="member_ids[]"]:checked').length;
        const occupancy = Number(form.dataset.occupancy);
        const capacity = Number(form.dataset.capacity);
        const remaining = capacity - occupancy - arriving;
        form.querySelector('[data-arrival-summary]').textContent =
            'Arriving: ' + arriving + ' people. Occupancy after check-in: ' +
            (occupancy + arriving) + '. Remaining capacity: ' + remaining +
            ' people.' + (remaining < 0 ? ' Not enough capacity.' : '');
    }
    form.addEventListener('change', updateSummary);
    updateSummary();
});
