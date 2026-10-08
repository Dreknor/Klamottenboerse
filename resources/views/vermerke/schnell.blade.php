<x-layouts.tablet titel="Vermerk erfassen" :zurueck="$zurueck">
    <form method="post" action="{{ route('vermerk.speichern') }}" class="mx-auto max-w-2xl space-y-5 rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        <input type="hidden" name="quelle" value="{{ $quelle }}">
        <input type="hidden" name="zurueck" value="{{ $zurueck }}">

        <p class="text-base text-stone-600">Für die Reputation der Verkäufer{{ $boerse ? ' · '.$boerse->titel : '' }}. Bei schlechter Reputation gibt es künftig keine automatische Nummer mehr.</p>

        <div>
            <label for="nummer" class="mb-1 block font-medium">Verkäufernummer</label>
            <input id="nummer" name="nummer" value="{{ old('nummer', $nummer) }}" inputmode="numeric" class="feld w-40 text-3xl" required @if (! old('nummer', $nummer)) autofocus @endif>
            @error('nummer')<p class="mt-1 text-base text-red-700">{{ $message }}</p>@enderror
        </div>

        <fieldset>
            <legend class="mb-2 font-medium">Was ist vorgefallen?</legend>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($arten as $art)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-300 px-4 py-3 has-[:checked]:border-marke-500 has-[:checked]:bg-marke-50">
                        <input type="radio" name="vermerk_art_id" value="{{ $art->id }}" class="h-5 w-5" @checked(old('vermerk_art_id') == $art->id) required>
                        <span>{{ $art->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('vermerk_art_id')<p class="mt-1 text-base text-red-700">{{ $message }}</p>@enderror
        </fieldset>

        <div>
            <label for="bemerkung" class="mb-1 block font-medium">Bemerkung (optional)</label>
            <input id="bemerkung" name="bemerkung" value="{{ old('bemerkung') }}" class="feld" placeholder="z. B. 2 von 3 Kisten fehlen">
        </div>

        <div class="flex flex-wrap gap-3">
            <x-ui.knopf groesse="gross">Vermerk speichern</x-ui.knopf>
            <x-ui.knopf groesse="gross" art="sekundaer" :href="$zurueck">Abbrechen</x-ui.knopf>
        </div>
    </form>
</x-layouts.tablet>
