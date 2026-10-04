<x-layouts.oeffentlich titel="Teilnahme absagen">
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        @if ($erledigt || $teilnahme->status === \App\Enums\TeilnahmeStatus::Abgesagt)
            <h1>Abgesagt</h1>
            <p class="mt-3 text-stone-600">Danke für die Rückmeldung! Deine Nummer geht automatisch an die nächste Person auf der Warteliste.</p>
        @else
            <h1>Teilnahme absagen?</h1>
            <p class="mt-3 text-stone-600">
                {{ $teilnahme->person?->vorname }}, möchtest du deine Teilnahme an der Klamottenbörse am
                {{ $teilnahme->boerse->verkaufstag->isoFormat('D. MMMM YYYY') }}
                @if ($teilnahme->nummer) (Nummer {{ $teilnahme->nummer }}) @endif wirklich absagen?
            </p>
            <form method="post" class="mt-6">
                @csrf
                <x-ui.knopf art="gefahr" groesse="gross">Ja, absagen</x-ui.knopf>
            </form>
        @endif
    </div>
</x-layouts.oeffentlich>
