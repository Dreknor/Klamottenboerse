<x-layouts.admin titel="Feedback">
    <x-ui.kopf titel="Feedback" :unter="$beantwortet.' von '.$verschickt.' Befragten haben geantwortet. Die Umfrage geht automatisch über den Mailplan raus.'">
        @if (auth()->user()->istOrga())
            <x-ui.knopf art="sekundaer" :href="route('admin.feedback.fragen.index')">Fragen bearbeiten</x-ui.knopf>
        @endif
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse ($fragen as ['frage' => $frage, 'antworten' => $antworten, 'schnitt' => $schnitt, 'verteilung' => $verteilung])
            <x-ui.karte :titel="$frage->text" @class(['lg:col-span-2' => $frage->typ === 'text'])>
                <p class="-mt-2 mb-3 text-sm text-stone-500">{{ $antworten->count() }} {{ $antworten->count() === 1 ? 'Antwort' : 'Antworten' }}
                    @if ($frage->rolle) · {{ \App\Models\FeedbackFrage::ROLLEN[$frage->rolle] }} @endif
                    @unless ($frage->aktiv) · nicht mehr in der Umfrage @endunless</p>

                @if ($frage->typ === 'sterne')
                    <p class="mb-3 text-3xl font-semibold">{{ $schnitt ? number_format($schnitt, 1, ',', '') : '–' }} <span class="text-base text-stone-500">/ 5</span></p>
                @endif

                @if ($verteilung)
                    @php $max = max(1, $verteilung->max()); @endphp
                    @foreach ($verteilung as $beschriftung => $anzahl)
                        <div class="mb-1 grid grid-cols-[minmax(3rem,12rem)_1fr_2rem] items-center gap-2 text-sm">
                            <span class="truncate">{{ $beschriftung }}</span>
                            <div class="h-3 rounded bg-stone-100"><div class="h-3 rounded {{ $frage->typ === 'sterne' ? 'bg-amber-400' : 'bg-marke-500' }}" style="width: {{ $anzahl / $max * 100 }}%"></div></div>
                            <span class="text-right tabular-nums">{{ $anzahl }}</span>
                        </div>
                    @endforeach
                @else
                    @forelse ($antworten as $a)
                        <div class="border-b border-stone-100 py-2 last:border-0">
                            <p class="whitespace-pre-line">{{ $a->text }}</p>
                            <p class="text-xs text-stone-500">{{ $a->feedback->rolle === 'helfer' ? 'Helfer/in' : 'Verkäufer/in' }} · {{ $a->feedback->beantwortet_at?->format('d.m.Y') }}</p>
                        </div>
                    @empty
                        <p class="text-stone-500">Noch keine Antworten.</p>
                    @endforelse
                @endif
            </x-ui.karte>
        @empty
            <x-ui.karte><p class="text-stone-500">Es sind keine Fragen angelegt.</p></x-ui.karte>
        @endforelse
    </div>
</x-layouts.admin>
