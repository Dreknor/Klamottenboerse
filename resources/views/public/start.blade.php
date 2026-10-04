<x-layouts.oeffentlich>
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 md:p-10">
        <h1 class="text-3xl md:text-4xl">Willkommen bei der Klamottenbörse!</h1>
        <p class="mt-3 text-lg text-stone-600">Sortierter Kindersachenflohmarkt zugunsten des Ev. Kinderhauses – organisiert von ehrenamtlichen Eltern.</p>

        @if ($boerse)
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-marke-50 p-4">
                    <p class="text-sm font-medium text-marke-800">Wann?</p>
                    <p class="mt-1 font-semibold">{{ $infos['datum'] }}</p>
                    <p class="text-sm">{{ $infos['verkauf'] }}</p>
                </div>
                <div class="rounded-xl bg-marke-50 p-4">
                    <p class="text-sm font-medium text-marke-800">Wo?</p>
                    <p class="mt-1 font-semibold">{{ $boerse->ort?->name }}</p>
                    <p class="text-sm">{{ $boerse->ort?->adresse }}</p>
                </div>
                <div class="rounded-xl bg-marke-50 p-4">
                    <p class="text-sm font-medium text-marke-800">Verkaufen?</p>
                    @if ($boerse->anmeldung_ab && $boerse->anmeldung_ab->isFuture())
                        <p class="mt-1 font-semibold">Anmeldung ab {{ $infos['anmeldung_ab'] }}</p>
                        @if ($boerse->anmeldung_kinderhaus_ab)<p class="text-sm">Kinderhaus-Familien ab {{ $infos['anmeldung_kinderhaus_ab'] }}</p>@endif
                    @else
                        <p class="mt-1 font-semibold">Anmeldung ist geöffnet</p>
                    @endif
                    <a href="{{ route('anmeldung.create') }}" class="mt-2 inline-block font-medium">Zur Anmeldung →</a>
                </div>
            </div>
        @else
            <p class="mt-6 rounded-xl bg-stone-50 p-4">Der Termin für die nächste Klamottenbörse steht noch nicht fest.</p>
        @endif
    </section>

    @if ($boerse && $freieSchichten > 0)
        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Du willst helfen?</h2>
            <p class="mt-2 text-stone-600">Die Klamottenbörse funktioniert nur mit vielen helfenden Händen. Es sind noch {{ $freieSchichten }} Plätze in Schichten frei.</p>
            <x-ui.knopf :href="route('helfer.index')" class="mt-4">Zur Helferliste</x-ui.knopf>
        </section>
    @endif

    <section class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>So läuft es ab</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-stone-700">
                <li>Online anmelden und Verkäufernummer erhalten.</li>
                <li>Artikel beschriften – von Hand oder mit Etiketten aus deinem Portal.</li>
                <li>Kiste abgeben{{ $boerse && $infos['anlieferung'] ? ': '.$infos['anlieferung'] : '' }}.</li>
                <li>Wir verkaufen für dich.</li>
                <li>Restware und Erlös abholen{{ $boerse && $infos['abholung'] ? ': '.$infos['abholung'] : '' }}.</li>
            </ol>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Kosten</h2>
            <p class="mt-3 text-stone-700">Es gibt keine Standgebühr. Dafür spenden die Verkäufer {{ $infos['provision'] ?? '25' }} % des Erlöses an das Kinderhaus – direkt bei der Abrechnung.</p>
        </div>
    </section>
</x-layouts.oeffentlich>
