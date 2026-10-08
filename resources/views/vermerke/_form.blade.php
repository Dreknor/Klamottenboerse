{{--
    Formularfelder zur Erfassung eines Verkäufer-Vermerks.
    Optionale Variablen: $vknummer (vorbelegt), $interessentId (statt VK-Nummer),
    $quelle, $prefix (eindeutige IDs bei mehreren Formularen auf einer Seite).
--}}
@php($prefix = $prefix ?? 'vermerk')
@csrf
<input type="hidden" name="quelle" value="{{ $quelle ?? '' }}">

@if (!empty($interessentId))
    <input type="hidden" name="interessent_id" value="{{ $interessentId }}">
@else
    <div class="form-group">
        <label for="{{ $prefix }}_vknummer">Verkäufernummer</label>
        <input type="number" name="vknummer" id="{{ $prefix }}_vknummer" class="form-control js-vermerk-vknummer"
               value="{{ old('vknummer', $vknummer ?? '') }}" required>
    </div>
@endif

<div class="form-group">
    <label for="{{ $prefix }}_typ">Was ist passiert?</label>
    <select name="typ" id="{{ $prefix }}_typ" class="form-control" required>
        @foreach (\App\Model\VerkaeuferVermerk::typen() as $key => $typ)
            <option value="{{ $key }}" @selected(old('typ') === $key)>{{ $typ->label }} ({{ $typ->punkte }} Pkt.)</option>
        @endforeach
    </select>
</div>

@can('access-verwaltung')
    <div class="form-group">
        <label for="{{ $prefix }}_punkte">Punkte (leer = Standard)</label>
        <input type="number" name="punkte" id="{{ $prefix }}_punkte" class="form-control" min="0" max="10" value="{{ old('punkte') }}">
    </div>
@endcan

<div class="form-group">
    <label for="{{ $prefix }}_bemerkung">Bemerkung (optional)</label>
    <input type="text" name="bemerkung" id="{{ $prefix }}_bemerkung" class="form-control" maxlength="1000" value="{{ old('bemerkung') }}">
</div>
