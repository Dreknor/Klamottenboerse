<x-layouts.oeffentlich :titel="$seite->slug === 'start' ? null : $titel" :beschreibung="$seite->beschreibung">
    @if ($vorschau)
        <div class="mb-6 rounded-xl border-2 border-dashed border-marke-500 bg-marke-50 px-4 py-3 text-marke-800">
            <strong>Vorschau des Entwurfs</strong> – so sieht die Seite nach dem Veröffentlichen aus. Besucher sehen noch die bisherige Fassung.
        </div>
    @endif
    @if ($seite->slug !== 'start' && ! collect($bloecke)->contains('typ', 'kopf'))
        <h1 class="mb-6 text-3xl">{{ $titel }}</h1>
    @endif
    @include('website.seite', ['bloecke' => $bloecke, 'k' => $k])
</x-layouts.oeffentlich>
