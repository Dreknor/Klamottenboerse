<x-layouts.oeffentlich titel="Anmeldung">
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        @if ($zugeteilt || $teilnahme->hatNummer())
            <p class="text-lg">Hallo {{ $person->vorname }}, du bist dabei! Deine Verkäufernummer:</p>
            <p class="my-4 text-6xl font-bold text-marke-700">{{ $teilnahme->nummer }}</p>
            <p class="text-stone-600">Alle Infos haben wir dir per Mail geschickt. In deinem Portal kannst du – wenn du möchtest – Artikel erfassen und Etiketten drucken.</p>
            <x-ui.knopf :href="route('portal.index')" class="mt-6">Zu meinem Portal</x-ui.knopf>
        @elseif ($teilnahme->status === \App\Enums\TeilnahmeStatus::Warteliste)
            <p class="text-5xl">⏳</p>
            <h1 class="mt-4">Du stehst auf der Warteliste</h1>
            <p class="mt-3 text-stone-600">Gerade sind alle Plätze vergeben (Platz {{ $teilnahme->wartelisten_position }} auf der Warteliste). Sobald ein Platz frei wird, bekommst du automatisch ein Angebot per Mail.</p>
        @elseif ($teilnahme->status === \App\Enums\TeilnahmeStatus::Angefragt)
            <p class="text-5xl">✉️</p>
            <h1 class="mt-4">Deine Anfrage ist angekommen</h1>
            <p class="mt-3 text-stone-600">Deine Verkäufernummer vergibt das Orga-Team diesmal persönlich. Wir melden uns per Mail bei dir, sobald wir über deine Anfrage entschieden haben.</p>
        @else
            <h1>Deine Anmeldung</h1>
            <p class="mt-3">Status: {{ $teilnahme->status->label() }}</p>
        @endif
    </div>
</x-layouts.oeffentlich>
