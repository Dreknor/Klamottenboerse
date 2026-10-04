<x-layouts.tablet titel="Annahme" :zurueck="route('tablet.index')">
    <p class="mb-3 text-stone-600">{{ $angeliefert }} von {{ $gesamt }} Nummern angeliefert</p>

    @include('tablet._suche', ['label' => 'Nummer (vom Kistenzettel scannen) oder Name'])

    @foreach ($treffer as $t)
        <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-4xl font-bold">{{ $t->nummer }}</p>
                    <p class="text-xl">{{ $t->anzeigeName() }}</p>
                </div>
                @if ($t->angeliefert_at)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">angenommen {{ $t->angeliefert_at->format('H:i') }}</span>
                @endif
            </div>
            <form method="post" action="{{ route('tablet.annehmen', $t) }}" class="mt-4 grid gap-3 md:grid-cols-4 md:items-end" x-data="{ kisten: {{ max(1, $t->kisten()->count()) }} }">
                @csrf
                <div>
                    <p class="mb-1 font-medium">Kisten</p>
                    <div class="flex items-center gap-2">
                        <button type="button" class="h-12 w-12 rounded-lg bg-stone-200 text-2xl" @click="kisten = Math.max(1, kisten - 1)">−</button>
                        <input name="kisten" x-model="kisten" class="feld w-16 text-center text-2xl" inputmode="numeric">
                        <button type="button" class="h-12 w-12 rounded-lg bg-stone-200 text-2xl" @click="kisten++">+</button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block font-medium" for="bemerkung-{{ $t->id }}">Bemerkung (optional)</label>
                    <input id="bemerkung-{{ $t->id }}" name="bemerkung" class="feld" placeholder="z. B. Jacke ohne Etikett zurückgegeben">
                </div>
                <button class="rounded-xl bg-emerald-600 py-4 text-lg font-semibold text-white">Angenommen ✓</button>
            </form>
        </div>
    @endforeach

    @if (request('suche') && $treffer->isEmpty())
        <p class="mt-4 rounded-xl bg-amber-100 p-4 text-amber-900">Nichts gefunden. Bitte Nummer prüfen oder Orga-Team holen.</p>
    @endif

    <details class="mt-6 rounded-2xl bg-white p-4 shadow-sm">
        <summary class="cursor-pointer font-medium">Noch nicht angeliefert ({{ $offen->count() }})</summary>
        <div class="mt-3 grid grid-cols-2 gap-2 text-base sm:grid-cols-4">
            @foreach ($offen as $t)
                <a href="{{ route('tablet.annahme', ['suche' => $t->nummer]) }}" class="rounded-lg bg-stone-100 px-3 py-2 text-stone-800 no-underline"><strong>{{ $t->nummer }}</strong> {{ $t->person?->nachname }}</a>
            @endforeach
        </div>
    </details>
</x-layouts.tablet>
