{{--
    Schnellerfassung eines Vermerks als Modal (Kasse, Kistenerfassung).
    Öffnen über ein Element mit data-toggle="modal" data-target="#vermerkModal";
    optional data-vknummer="123" zum Vorbelegen der Verkäufernummer.
--}}
<div id="vermerkModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="vermerkModalTitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('vermerke.store') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="vermerkModalTitle">Vorfall zu Verkäufer erfassen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('vermerke._form', ['quelle' => $quelle, 'prefix' => 'vermerkModal'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-warning">Vermerk speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ === 'undefined') {
            return;
        }
        $('#vermerkModal').on('show.bs.modal', function (event) {
            var vknummer = $(event.relatedTarget).data('vknummer');
            var input = $(this).find('.js-vermerk-vknummer');
            input.val(vknummer || '');
        }).on('shown.bs.modal', function () {
            var input = $(this).find('.js-vermerk-vknummer');
            (input.val() ? $(this).find('select[name=typ]') : input).trigger('focus');
        });
    });
</script>
