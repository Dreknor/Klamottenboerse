<section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
    <div class="grid items-center gap-6 p-6 md:p-10 {{ $b['logo'] ? 'md:grid-cols-5' : '' }}">
        <div class="{{ $b['logo'] ? 'md:col-span-3' : '' }}">
            <h1 class="text-3xl leading-tight md:text-4xl">{{ $k->zeile($b['titel']) }}</h1>
            @if ($b['text'])<div class="inhalt mt-3 text-lg">{!! $k->text($b['text']) !!}</div>@endif
            @if ($k->boerse)
                <div class="mt-6 rounded-xl bg-marke-50 p-4 ring-1 ring-marke-100">
                    <p class="text-sm font-medium uppercase tracking-wide text-marke-800">Nächste Klamottenbörse</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $k->infos['datum'] }}</p>
                    <p class="text-stone-700">{{ collect([$k->infos['verkauf'], $k->boerse->ort?->name])->filter()->implode(' · ') }}</p>
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <x-ui.knopf :href="route('anmeldung.create')" groesse="gross">Als Verkäufer anmelden</x-ui.knopf>
                    @if ($k->freieSchichten > 0)<x-ui.knopf :href="route('helfer.index')" art="sekundaer" groesse="gross">Helfen</x-ui.knopf>@endif
                </div>
            @else
                <p class="mt-6 rounded-xl bg-stone-50 p-4">Der Termin für die nächste Klamottenbörse steht noch nicht fest. Schau bald wieder vorbei!</p>
            @endif
        </div>
        @if ($b['logo'])
            <div class="md:col-span-2">
                <img src="{{ asset('images/logo-640.png') }}" alt="Zeichnung: Ein Kind hängt kopfüber an einer Wäscheleine zwischen Kinderkleidung" class="mx-auto w-full max-w-sm" width="640" height="494">
            </div>
        @endif
    </div>
</section>
