@php
    $galerie = [
        'DSC_7750-1-300x200.jpg' => 'Sortierte Kinderkleidung auf Tischen und an Kleiderständern',
        'DSC_7755-1-300x200.jpg' => 'Kleider und Röcke an der Kleiderstange',
        'DSC_7757-1-300x200.jpg' => 'Bunte Kinderjacken auf Bügeln',
        'DSC_7760-2-300x200.jpg' => 'Tische mit Hosen und Pullovern, nach Größen sortiert',
        'DSC_7770-300x200.jpg' => 'Blick in den Saal mit langen Verkaufstischen',
        'DSC_7785-300x200.jpg' => 'Spielzeugtiere auf dem Spielzeugtisch',
        'DSC_7786-300x200.jpg' => 'Brettspiele und Puzzles zum Verkauf',
        'DSC_7805-300x200.jpg' => 'Blick durch den Saal mit Sortiertischen',
    ];
@endphp
<x-layouts.oeffentlich>
    {{-- Kopfbereich --}}
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
        <div class="grid items-center gap-6 p-6 md:grid-cols-5 md:p-10">
            <div class="md:col-span-3">
                <h1 class="text-3xl leading-tight md:text-4xl">Sortierter Kindersachenflohmarkt in Radebeul</h1>
                <p class="mt-3 text-lg text-stone-600">Zweimal im Jahr zugunsten des Ev. Kinderhauses der Friedenskirchgemeinde – organisiert von ehrenamtlichen Eltern.</p>

                @if ($boerse)
                    <div class="mt-6 rounded-xl bg-marke-50 p-4 ring-1 ring-marke-100">
                        <p class="text-sm font-medium uppercase tracking-wide text-marke-800">Nächste Klamottenbörse</p>
                        <p class="mt-1 text-2xl font-semibold">{{ $infos['datum'] }}</p>
                        <p class="text-stone-700">{{ collect([$infos['verkauf'], $boerse->ort?->name])->filter()->implode(' · ') }}</p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-ui.knopf :href="route('anmeldung.create')" groesse="gross">Als Verkäufer anmelden</x-ui.knopf>
                        @if ($freieSchichten > 0)
                            <x-ui.knopf :href="route('helfer.index')" art="sekundaer" groesse="gross">Helfen</x-ui.knopf>
                        @endif
                    </div>
                @else
                    <p class="mt-6 rounded-xl bg-stone-50 p-4">Der Termin für die nächste Klamottenbörse steht noch nicht fest. Schau bald wieder vorbei!</p>
                @endif
            </div>
            <div class="md:col-span-2">
                <img src="{{ asset('images/logo-640.png') }}" alt="Zeichnung: Ein Kind hängt kopfüber an einer Wäscheleine zwischen Kinderkleidung" class="mx-auto w-full max-w-sm" width="640" height="494">
            </div>
        </div>
    </section>

    @if ($boerse)
        {{-- Termine --}}
        <section class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                <p class="text-sm font-medium text-marke-800">Anmeldung</p>
                @if ($boerse->anmeldung_ab && $boerse->anmeldung_ab->isFuture())
                    <p class="mt-1 font-semibold">ab {{ $infos['anmeldung_ab'] }}</p>
                    @if ($boerse->anmeldung_kinderhaus_ab)<p class="text-sm text-stone-600">Kinderhaus-Familien schon ab {{ $infos['anmeldung_kinderhaus_ab'] }}</p>@endif
                @else
                    <p class="mt-1 font-semibold">ist geöffnet</p>
                    <a href="{{ route('anmeldung.create') }}" class="text-sm font-medium">Jetzt anmelden →</a>
                @endif
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                <p class="text-sm font-medium text-marke-800">Kisten abgeben</p>
                <p class="mt-1 font-semibold">{{ $infos['anlieferung'] ?: 'wird noch bekannt gegeben' }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                <p class="text-sm font-medium text-marke-800">Ort</p>
                <p class="mt-1 font-semibold">{{ $boerse->ort?->name }}</p>
                <p class="text-sm text-stone-600">{{ $boerse->ort?->adresse }}</p>
            </div>
        </section>
    @endif

    {{-- Ablauf mit Foto --}}
    <section class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>So läuft es ab</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-stone-700">
                <li>Online anmelden und Verkäufernummer erhalten.</li>
                <li>Artikel beschriften – von Hand oder mit Etiketten aus deinem Portal.</li>
                <li>Kiste abgeben{{ $boerse && $infos['anlieferung'] ? ': '.$infos['anlieferung'] : '' }}.</li>
                <li>Wir sortieren und verkaufen für dich.</li>
                <li>Restware und Erlös abholen{{ $boerse && $infos['abholung'] ? ': '.$infos['abholung'] : '' }}.</li>
            </ol>
            <h2 class="mt-6">Kosten</h2>
            <p class="mt-2 text-stone-700">Es gibt keine Standgebühr. Dafür spenden die Verkäufer {{ $infos['provision'] ?? '25' }} % des Erlöses an das Kinderhaus – direkt bei der Abrechnung.</p>
        </div>
        <figure class="overflow-hidden rounded-2xl shadow-sm ring-1 ring-stone-200">
            <img src="{{ asset('images/DSC_7746-1-1024x684.jpg') }}" alt="Bunte Regenjacken und Matschhosen an einem Kleiderständer mit Schild „Regensachen“" class="h-full w-full object-cover" width="1024" height="684" loading="lazy">
        </figure>
    </section>

    {{-- Was verkauft wird --}}
    <section class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Das wird verkauft</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-stone-700">
                <li>saisonabhängige Kinderbekleidung, Matschkleidung</li>
                <li>Schuhe, Gummistiefel</li>
                <li>Kinderwagen, Auto- und Fahrradsitze, Tragehilfen</li>
                <li>Laufgitter, Kinderbetten</li>
                <li>Spielzeug, Bücher, Fahrzeuge</li>
            </ul>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Das nehmen wir nicht an</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-stone-700">
                <li>Babykleidung unter Größe 74</li>
                <li>Erwachsenenkleidung, Erstausstattung</li>
                <li>Plüschtiere, abgetragene Schuhe</li>
            </ul>
            <p class="mt-3 text-sm text-stone-500">Schwer verkäufliche Artikel behalten wir uns vor, in der Kiste zu lassen.</p>
        </div>
    </section>

    {{-- Eindrücke --}}
    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200" aria-labelledby="eindruecke">
        <h2 id="eindruecke">Eindrücke</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <img src="{{ asset('images/DSC_7821-768x513.jpg') }}" alt="Besucherinnen und Besucher stöbern an den Tischen" class="col-span-2 row-span-2 h-full w-full rounded-xl object-cover" width="768" height="513" loading="lazy">
            @foreach ($galerie as $datei => $alt)
                <img src="{{ asset('images/'.$datei) }}" alt="{{ $alt }}" class="aspect-[3/2] w-full rounded-xl object-cover" width="300" height="200" loading="lazy">
            @endforeach
        </div>
    </section>

    @if ($boerse && $freieSchichten > 0)
        <section class="mt-6 rounded-2xl bg-marke-50 p-6 ring-1 ring-marke-100">
            <h2>Du willst helfen?</h2>
            <p class="mt-2 text-stone-700">Die Klamottenbörse funktioniert nur mit vielen helfenden Händen. Es sind noch {{ $freieSchichten }} Plätze in Schichten frei.</p>
            <x-ui.knopf :href="route('helfer.index')" class="mt-4">Zur Helferliste</x-ui.knopf>
        </section>
    @endif
</x-layouts.oeffentlich>
