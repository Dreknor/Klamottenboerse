@if ($k->boerse)
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
        @if ($b['titel'])<h2 class="mb-4">{{ $b['titel'] }}</h2>@endif
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <p class="text-sm font-medium text-marke-800">Verkauf</p>
                <p class="font-semibold">{{ $k->infos['datum'] }}</p>
                <p class="text-sm text-stone-600">{{ $k->infos['verkauf'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-marke-800">Anmeldung</p>
                @if ($k->boerse->anmeldung_ab && $k->boerse->anmeldung_ab->isFuture())
                    <p class="font-semibold">ab {{ $k->infos['anmeldung_ab'] }}</p>
                    @if ($k->boerse->anmeldung_kinderhaus_ab)<p class="text-sm text-stone-600">Kinderhaus-Familien ab {{ $k->infos['anmeldung_kinderhaus_ab'] }}</p>@endif
                @else
                    <p class="font-semibold">ist geöffnet</p>
                    <a href="{{ route('anmeldung.create') }}" class="text-sm font-medium">Jetzt anmelden →</a>
                @endif
            </div>
            <div>
                <p class="text-sm font-medium text-marke-800">Kisten abgeben</p>
                <p class="font-semibold">{{ $k->infos['anlieferung'] ?: 'wird noch bekannt gegeben' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-marke-800">Ort</p>
                <p class="font-semibold">{{ $k->boerse->ort?->name }}</p>
                <p class="text-sm text-stone-600">{{ $k->boerse->ort?->adresse }}</p>
            </div>
        </div>
    </section>
@endif
