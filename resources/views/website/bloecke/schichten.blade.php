@if ($k->boerse && $k->freieSchichten > 0)
    <section class="rounded-2xl bg-marke-50 p-6 ring-1 ring-marke-100">
        <h2>{{ $b['titel'] }}</h2>
        <p class="mt-2 text-stone-700">
            {{ $b['text'] ? $k->zeile($b['text']) : 'Die Klamottenbörse funktioniert nur mit vielen helfenden Händen.' }}
            Es sind noch {{ $k->freieSchichten }} Plätze in Schichten frei.
        </p>
        <x-ui.knopf :href="route('helfer.index')" class="mt-4">Zur Helferliste</x-ui.knopf>
    </section>
@endif
