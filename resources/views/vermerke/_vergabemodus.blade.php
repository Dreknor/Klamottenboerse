{{-- Auswahl des Vergabemodus eines Interessenten. Erwartet $interessent. --}}
@php($modus = $interessent->nur_manuelle_vergabe === null ? 'auto' : ($interessent->nur_manuelle_vergabe ? 'manuell' : 'freigegeben'))
<form method="post" action="{{ route('vermerke.vergabemodus', $interessent->id) }}" class="form-inline">
    @csrf
    @method('put')
    <select name="modus" class="form-control form-control-sm mr-1" aria-label="Vergabemodus">
        <option value="auto" @selected($modus === 'auto')>automatisch nach Punkten</option>
        <option value="manuell" @selected($modus === 'manuell')>immer nur händisch</option>
        <option value="freigegeben" @selected($modus === 'freigegeben')>trotz Punkten freigegeben</option>
    </select>
    <button type="submit" class="btn btn-sm btn-secondary">setzen</button>
</form>
