<x-layouts.admin titel="Feedback">
    <x-ui.kopf titel="Feedback" :unter="$antworten->count().' von '.$verschickt.' Befragten haben geantwortet. Die Umfrage geht automatisch über den Mailplan raus.'" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.karte titel="Wie war die Börse?">
            <p class="mb-3 text-3xl font-semibold">{{ $schnitt ? number_format($schnitt, 1, ',', '') : '–' }} <span class="text-base text-stone-500">/ 5</span></p>
            @php $max = max(1, $verteilung->max()); @endphp
            @foreach ($verteilung as $sterne => $anzahl)
                <div class="mb-1 flex items-center gap-2 text-sm">
                    <span class="w-12">{{ str_repeat('★', $sterne) }}</span>
                    <div class="h-3 flex-1 rounded bg-stone-100"><div class="h-3 rounded bg-amber-400" style="width: {{ $anzahl / $max * 100 }}%"></div></div>
                    <span class="w-6 text-right">{{ $anzahl }}</span>
                </div>
            @endforeach
        </x-ui.karte>

        <x-ui.karte titel="Antworten" class="lg:col-span-2">
            @forelse ($antworten as $a)
                <div class="border-b border-stone-100 py-3 last:border-0">
                    <p class="text-sm text-stone-500">{{ $a->rolle === 'helfer' ? 'Helfer/in' : 'Verkäufer/in' }} · {{ $a->bewertung ? str_repeat('★', $a->bewertung) : 'ohne Bewertung' }} · {{ $a->beantwortet_at->format('d.m.Y') }}</p>
                    @if ($a->gut)<p class="mt-1"><span class="font-medium text-emerald-800">Gut:</span> {{ $a->gut }}</p>@endif
                    @if ($a->besser)<p class="mt-1"><span class="font-medium text-amber-800">Besser:</span> {{ $a->besser }}</p>@endif
                </div>
            @empty
                <p class="text-stone-500">Noch keine Antworten.</p>
            @endforelse
        </x-ui.karte>
    </div>
</x-layouts.admin>
