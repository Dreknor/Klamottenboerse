{{-- Checkboxen der Angebotskategorien, gruppiert. Erwartet $ausgewaehlt (Array von Keys). --}}
@php($ausgewaehlt = old('angebotskategorien', $ausgewaehlt ?? []))
@foreach (\App\Model\Angebotskategorie::aktive()->groupBy('gruppe', true) as $gruppe => $kategorien)
    <fieldset class="mb-2">
        <legend class="h6 mb-1">{{ $gruppe }}</legend>
        @foreach ($kategorien as $key => $kategorie)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="angebotskategorien[]" value="{{ $key }}"
                       id="kat_{{ $key }}" @checked(in_array($key, $ausgewaehlt ?? [], true))>
                <label class="form-check-label" for="kat_{{ $key }}">{{ $kategorie->label }}</label>
            </div>
        @endforeach
    </fieldset>
@endforeach
