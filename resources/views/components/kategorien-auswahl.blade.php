{{-- Ankreuzliste „Was bringst du überwiegend mit?“ – gruppiert (Kleidung, Weiteres …). --}}
@props(['ausgewaehlt' => [], 'legende' => 'Was bringst du überwiegend mit?', 'hilfe' => 'Mehrere Angaben möglich. Das hilft uns bei der Planung der Tische.'])
@php
    $gruppen = \App\Models\Kategorie::auswahl();
    $ausgewaehlt = array_map('intval', (array) old('kategorien', $ausgewaehlt));
@endphp
@if ($gruppen->isNotEmpty())
    <fieldset {{ $attributes }}>
        <legend class="font-medium">{{ $legende }}</legend>
        @if ($hilfe)<p class="text-sm text-stone-500">{{ $hilfe }}</p>@endif
        <input type="hidden" name="kategorien_gesendet" value="1">
        <div class="mt-2 grid gap-x-6 gap-y-3 sm:grid-cols-2">
            @foreach ($gruppen as $gruppe => $kategorien)
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-stone-500">{{ $gruppe }}</p>
                    @foreach ($kategorien as $kategorie)
                        <label class="mt-1 flex items-start gap-2">
                            <input type="checkbox" name="kategorien[]" value="{{ $kategorie->id }}" class="mt-1" @checked(in_array($kategorie->id, $ausgewaehlt, true))>
                            <span>{{ $kategorie->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
        @error('kategorien.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </fieldset>
@endif
