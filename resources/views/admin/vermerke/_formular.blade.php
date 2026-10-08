{{-- Vermerk erfassen (Backend). Erwartet $arten, optional $person. --}}
<form method="post" action="{{ route('admin.vermerke.store') }}" class="space-y-4">
    @csrf
    @if ($person ?? null)
        <input type="hidden" name="person_id" value="{{ $person->id }}">
    @else
        <x-ui.personen-auswahl label="Verkäufer" />
        @error('person_id')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    @endif
    <fieldset>
        <legend class="mb-1 text-sm font-medium">Was ist vorgefallen?</legend>
        <div class="grid gap-1 sm:grid-cols-2">
            @foreach ($arten as $art)
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="vermerk_art_id" value="{{ $art->id }}" @checked(old('vermerk_art_id') == $art->id) required>
                    {{ $art->name }} <span class="text-stone-500">({{ $art->punkte }} {{ $art->punkte === 1 ? 'Punkt' : 'Punkte' }})</span>
                </label>
            @endforeach
        </div>
        @error('vermerk_art_id')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    </fieldset>
    <x-ui.feld name="bemerkung" label="Bemerkung (optional)" />
    <x-ui.knopf>Vermerk speichern</x-ui.knopf>
</form>
