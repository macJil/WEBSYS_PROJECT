(() => {
    const form = document.getElementById("record-selection");
    if (!form) return;
    const boxes = Array.from(form.querySelectorAll('[name="ids[]"]')),
        all = document.getElementById("select-page"),
        count = document.getElementById("selected-count");
    function update() {
        const n = boxes.filter((b) => b.checked).length;
        count.textContent = n + " selected";
        all.checked = boxes.length > 0 && n === boxes.length;
        all.indeterminate = n > 0 && n < boxes.length;
    }
    all.addEventListener("change", () => {
        boxes.forEach((b) => (b.checked = all.checked));
        update();
    });
    boxes.forEach((b) => b.addEventListener("change", update));
    form.addEventListener("submit", (event) => {
        const selected = boxes.filter((box) => box.checked).length;
        if (!selected) {
            event.preventDefault();
            alert("Select at least one record to delete.");
        } else if (!confirm("Permanently delete " + selected + " selected record(s)? This cannot be undone.")) {
            event.preventDefault();
        }
    });
    update();
})();
