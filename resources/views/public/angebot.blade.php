<x-layouts.oeffentlich titel="Platz frei">
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        @if ($teilnahme->status === \App\Enums\TeilnahmeStatus::Angeboten && $teilnahme->angebot_bis?->isFuture())
            <h1>Ein Platz ist frei geworden!</h1>
            <p class="mt-3 text-stone-600">Für die Klamottenbörse am {{ $teilnahme->boerse->verkaufstag->isoFormat('D. MMMM YYYY') }} ist die Nummer</p>
            <p class="my-4 text-6xl font-bold text-marke-700">{{ $teilnahme->nummer }}</p>
            <p class="text-stone-600">für dich reserviert – bis {{ $teilnahme->angebot_bis->isoFormat('dddd, D. MMMM, H:mm [Uhr]') }}.</p>
            <form method="post" class="mt-6">@csrf<x-ui.knopf art="erfolg" groesse="gross">Nummer annehmen</x-ui.knopf></form>
        @elseif ($teilnahme->hatNummer())
            <h1>Du bist dabei</h1>
            <p class="mt-3">Deine Nummer: <strong>{{ $teilnahme->nummer }}</strong></p>
        @else
            <h1>Angebot abgelaufen</h1>
            <p class="mt-3 text-stone-600">Leider ist dieses Angebot nicht mehr gültig.</p>
        @endif
    </div>
</x-layouts.oeffentlich>
