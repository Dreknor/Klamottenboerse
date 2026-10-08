<x-layouts.admin titel="System">
    <x-ui.kopf titel="System" unter="Zustand der Installation, Fehler und Updates – ohne SSH." />

    @if (\App\Support\Demo::aktiv())
        <x-ui.karte titel="Demo zurücksetzen" class="mb-6 border-amber-300 bg-amber-50">
            <p class="text-sm">Setzt alle Daten auf frische Beispieldaten zurück – passiert sonst jede Nacht um {{ config('demo.zuruecksetzen_um') }} Uhr automatisch.
                Alle Testenden werden dabei abgemeldet. Updates sind in der Demo abgeschaltet.</p>
            <form method="post" action="{{ route('admin.demo.zuruecksetzen') }}" class="mt-3" onsubmit="return confirm('Alle Demo-Daten jetzt zurücksetzen?')">
                @csrf
                <x-ui.knopf>Jetzt zurücksetzen</x-ui.knopf>
            </form>
        </x-ui.karte>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.karte titel="Zustand">
            <ul class="divide-y divide-stone-100">
                @foreach ($pruefungen as $p)
                    <li class="flex items-start gap-3 py-2">
                        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs text-white {{ $p['ok'] === null ? 'bg-stone-300' : ($p['ok'] ? 'bg-emerald-600' : 'bg-red-600') }}"
                              aria-label="{{ $p['ok'] === null ? 'nicht genutzt' : ($p['ok'] ? 'in Ordnung' : 'Problem') }}">{{ $p['ok'] === null ? '–' : ($p['ok'] ? '✓' : '!') }}</span>
                        <span><strong class="font-medium">{{ $p['titel'] }}</strong><br><span class="text-sm text-stone-600">{{ $p['text'] }}</span></span>
                    </li>
                @endforeach
            </ul>
        </x-ui.karte>

        <x-ui.karte titel="Fehlerprotokoll">
            <x-slot:aktionen><x-ui.knopf :href="route('admin.fehler.index')" art="sekundaer" groesse="klein">Alle ansehen</x-ui.knopf></x-slot:aktionen>
            @if ($offeneFehler === 0)
                <p class="text-emerald-700">Keine offenen Fehler. 🎉</p>
            @else
                <p class="mb-2 text-sm text-stone-600">{{ $offeneFehler }} offene Einträge – die neuesten:</p>
                <ul class="divide-y divide-stone-100 text-sm">
                    @foreach ($neuesteFehler as $f)
                        <li class="py-2">
                            <a href="{{ route('admin.fehler.show', $f) }}" class="line-clamp-2">{{ $f->nachricht }}</a>
                            <span class="text-xs text-stone-500">{{ $f->zuletzt_at?->diffForHumans() }} · {{ $f->anzahl }}×</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.karte>

        <x-ui.karte titel="Software-Update" class="lg:col-span-2">
            @unless ($updateMoeglich)
                <p class="text-amber-800">Updates aus der Weboberfläche sind auf diesem Server nicht möglich (kein Git-Checkout oder PHP darf keine Programme starten). Bitte wie bisher per <code>deploy.sh</code> aktualisieren.</p>
            @else
                <p class="text-sm text-stone-600">
                    Installiert: <strong class="font-mono">{{ $version['commit'] ?? '?' }}</strong>
                    @if ($version['datum'])vom {{ \Illuminate\Support\Carbon::parse($version['datum'])->isoFormat('D. MMMM YYYY, HH:mm') }}@endif
                    · Zweig {{ $version['branch'] }}
                    @if ($version['text'])<br><span class="text-stone-500">„{{ $version['text'] }}“</span>@endif
                </p>

                <div class="mt-4 flex flex-wrap items-start gap-4">
                    <form method="post" action="{{ route('admin.system.pruefen') }}">@csrf<x-ui.knopf art="sekundaer">Nach Updates suchen</x-ui.knopf></form>
                    @if ($pruefung)
                        <span class="text-sm text-stone-500">zuletzt geprüft {{ $pruefung['zeit']->diffForHumans() }}</span>
                    @endif
                </div>

                @if ($pruefung && $pruefung['commits'])
                    <div class="mt-4 rounded-lg border border-marke-200 bg-marke-50 p-4">
                        <p class="font-medium">{{ count($pruefung['commits']) }} neue Änderung(en):</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($pruefung['commits'] as $c)
                                <li>{{ $c['text'] }} <span class="text-stone-500">({{ \Illuminate\Support\Carbon::parse($c['datum'])->isoFormat('D.M.YYYY') }})</span></li>
                            @endforeach
                        </ul>
                        <form method="post" action="{{ route('admin.system.update') }}" class="mt-4 space-y-3"
                              x-data="{ laeuft: false }" @submit="laeuft = true">
                            @csrf
                            <label class="flex items-start gap-2 text-sm">
                                <input type="checkbox" name="bestaetigung" value="1" class="mt-0.5" required>
                                <span>Gerade kassiert niemand und es läuft keine Annahme. Die Seite ist während des Updates (meist unter einer Minute) kurz nicht erreichbar.</span>
                            </label>
                            @error('bestaetigung')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                            <x-ui.knopf x-bind:disabled="laeuft"><span x-text="laeuft ? 'Update läuft … bitte warten' : 'Update jetzt installieren'">Update jetzt installieren</span></x-ui.knopf>
                        </form>
                    </div>
                @elseif ($pruefung)
                    <p class="mt-4 text-emerald-700">Die Software ist auf dem neuesten Stand.</p>
                @endif
            @endunless

            @if ($updates->isNotEmpty())
                <h3 class="mt-6 font-medium">Bisherige Updates</h3>
                <div class="mt-2 space-y-2">
                    @foreach ($updates as $u)
                        <details class="rounded-lg border border-stone-200" @if ($loop->first && $u->status !== 'erfolgreich') open @endif>
                            <summary class="flex cursor-pointer flex-wrap items-center gap-2 px-3 py-2 text-sm">
                                <x-ui.abzeichen :farbe="match ($u->status) { 'erfolgreich' => 'emerald', 'laeuft' => 'amber', default => 'red' }">{{ $u->status }}</x-ui.abzeichen>
                                {{ $u->created_at->isoFormat('D.M.YYYY HH:mm') }} · {{ $u->person?->name ?? 'unbekannt' }}
                                <span class="font-mono text-xs text-stone-500">{{ \Illuminate\Support\Str::limit($u->von_version, 7, '') }} → {{ \Illuminate\Support\Str::limit($u->auf_version ?? '–', 7, '') }}</span>
                            </summary>
                            <pre class="max-h-96 overflow-auto whitespace-pre-wrap border-t border-stone-200 bg-stone-50 p-3 text-xs">{{ $u->ausgabe }}</pre>
                        </details>
                    @endforeach
                </div>
            @endif
        </x-ui.karte>
    </div>
</x-layouts.admin>
