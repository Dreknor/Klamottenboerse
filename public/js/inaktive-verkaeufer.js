(function () {
    var selectPage = document.getElementById('select-page');
    var selections = document.querySelectorAll('.seller-selection:not(:disabled)');
    var button = document.getElementById('delete-selected');
    var count = document.getElementById('selected-count');

    function update() {
        var selected = 0;
        for (var i = 0; i < selections.length; i++) {
            if (selections[i].checked) {
                selected++;
            }
        }
        count.textContent = selected;
        button.disabled = selected === 0;
        selectPage.checked = selections.length > 0 && selected === selections.length;
        selectPage.indeterminate = selected > 0 && selected < selections.length;
    }

    selectPage.addEventListener('change', function () {
        for (var i = 0; i < selections.length; i++) {
            selections[i].checked = selectPage.checked;
        }
        update();
    });
    for (var i = 0; i < selections.length; i++) {
        selections[i].addEventListener('change', update);
    }
    document.getElementById('verkaeufer-loeschen').addEventListener('submit', function (event) {
        if (!window.confirm('Die ausgewählten Einträge wirklich löschen und per E-Mail informieren?')) {
            event.preventDefault();
        } else {
            button.disabled = true;
        }
    });
    update();
})();
