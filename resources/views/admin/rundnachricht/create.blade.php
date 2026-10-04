<x-layouts.admin titel="Nachricht schreiben">
    <x-ui.kopf titel="Nachricht schreiben" unter="An einzelne Personen oder ganze Gruppen – per E-Mail und, wer es eingeschaltet hat, als Push aufs Handy." />

    @php
        $start = [
            'gruppen' => old('gruppen', []),
            'personen' => $vorauswahl,
            'url' => route('admin.rundnachricht.vorschau'),
        ];
    @endphp
    <form method="post" action="{{ route('admin.rundnachricht.store') }}" x-data="rundnachricht(@js($start))" class="grid gap-6 lg:grid-cols-3">
        @csrf
        <div class="space-y-6 lg:col-span-2">
            <x-ui.karte titel="An wen?">
                <fieldset>
                    <legend class="mb-2 text-sm font-medium">Gruppen @if ($boerse)<span class="font-normal text-stone-500">– bezogen auf {{ $boerse->titel }}</span>@endif</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($gruppen as $g)
                            @php $moeglich = $boerse || in_array($g, $ohneBoerse, true); @endphp
                            <label class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm {{ $moeglich ? 'border-stone-200 hover:bg-stone-50' : 'border-stone-100 text-stone-400' }}">
                                <input type="checkbox" name="gruppen[]" value="{{ $g->value }}" x-model="gruppen" @disabled(! $moeglich)>
                                {{ $g->label() }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-5">
                    <x-ui.personen-auswahl label="Einzelne Personen hinzufügen" :mehrfach="true" x-on:person-gewaehlt="hinzufuegen($event.detail)" />
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="p in personen" :key="p.id">
                            <span class="inline-flex items-center gap-1 rounded-full bg-marke-50 px-3 py-1 text-sm text-marke-900">
                                <input type="hidden" name="personen[]" :value="p.id">
                                <span x-text="p.name"></span>
                                <button type="button" class="ml-1 text-marke-700" @click="entfernen(p.id)" :aria-label="p.name + ' entfernen'">✕</button>
                            </span>
                        </template>
                    </div>
                </div>
            </x-ui.karte>

            <x-ui.karte titel="Nachricht">
                <div class="space-y-4">
                    <x-ui.feld name="betreff" label="Betreff" required />
                    <div>
                        <label for="text" class="mb-1 block text-sm font-medium">Text</label>
                        <textarea id="text" name="text" rows="10" class="feld" required>{{ old('text', "Hallo {vorname},\n\n") }}</textarea>
                        <p class="mt-1 text-xs text-stone-500">
                            Platzhalter werden für jede Person ersetzt:
                            @foreach ($platzhalter as $name => $beschreibung)
                                <code title="{{ $beschreibung }}">{{ '{'.$name.'}' }}</code>@if (! $loop->last), @endif
                            @endforeach
                            · Links: <code>[Text](https://…)</code>
                        </p>
                    </div>
                </div>
            </x-ui.karte>
        </div>

        <div class="space-y-6">
            <x-ui.karte titel="Versand">
                <label class="flex items-center gap-2"><input type="checkbox" name="mail" value="1" checked> per E-Mail</label>
                <label class="mt-2 flex items-center gap-2"><input type="checkbox" name="push" value="1" checked> zusätzlich als Push</label>

                <div class="mt-4 rounded-lg bg-stone-50 p-3 text-sm" aria-live="polite">
                    <template x-if="vorschau">
                        <div>
                            <p class="text-2xl font-bold" x-text="vorschau.gesamt + ' Empfänger'"></p>
                            <p class="text-stone-600"><span x-text="vorschau.mail"></span> per E-Mail · <span x-text="vorschau.push"></span> mit Push</p>
                            <details class="mt-2" x-show="vorschau.namen.length">
                                <summary class="cursor-pointer text-marke-700">Namen anzeigen</summary>
                                <p class="mt-1 text-xs text-stone-600" x-text="vorschau.namen.join(' · ') + (vorschau.gesamt > vorschau.namen.length ? ' …' : '')"></p>
                            </details>
                        </div>
                    </template>
                    <p x-show="!vorschau" class="text-stone-500">Gruppen oder Personen wählen …</p>
                </div>

                <x-ui.knopf class="mt-4 w-full" x-bind:disabled="!vorschau || !vorschau.gesamt">Nachricht senden</x-ui.knopf>
                <p class="mt-2 text-xs text-stone-500">E-Mails gehen im Rahmen des Stundenlimits raus – Einzelmails haben Vorrang.</p>
            </x-ui.karte>
        </div>
    </form>

    <script>
        function rundnachricht({ gruppen, personen, url }) {
            return {
                gruppen,
                personen,
                vorschau: null,
                init() {
                    this.$watch('gruppen', () => this.aktualisieren());
                    this.$watch('personen', () => this.aktualisieren());
                    this.aktualisieren();
                },
                hinzufuegen(p) {
                    if (!this.personen.some((x) => x.id === p.id)) this.personen = [...this.personen, p];
                },
                entfernen(id) {
                    this.personen = this.personen.filter((p) => p.id !== id);
                },
                async aktualisieren() {
                    if (!this.gruppen.length && !this.personen.length) {
                        this.vorschau = null;
                        return;
                    }
                    const antwort = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ gruppen: this.gruppen, personen: this.personen.map((p) => p.id) }),
                    });
                    if (antwort.ok) this.vorschau = await antwort.json();
                },
            };
        }
    </script>
</x-layouts.admin>
