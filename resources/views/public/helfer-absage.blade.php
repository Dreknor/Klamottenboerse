<x-layouts.oeffentlich titel="Schicht absagen">
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        @php $s = $einteilung->schicht; @endphp
        @if ($erledigt || $einteilung->status === \App\Enums\EinteilungStatus::Abgesagt)
            <h1>Abgesagt</h1>
            <p class="mt-3 text-stone-600">Schade – danke, dass du Bescheid gibst. Vielleicht klappt es beim nächsten Mal!</p>
        @else
            <h1>Schicht absagen?</h1>
            <p class="mt-3 text-stone-600">{{ $s->bereich }}, {{ $s->beginn->isoFormat('dddd, D. MMMM, H:mm') }}–{{ $s->ende->format('H:i') }} Uhr</p>
            <form method="post" class="mt-6">@csrf<x-ui.knopf art="gefahr" groesse="gross">Ja, absagen</x-ui.knopf></form>
        @endif
    </div>
</x-layouts.oeffentlich>
