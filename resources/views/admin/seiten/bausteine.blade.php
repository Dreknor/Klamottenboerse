<x-layouts.admin :titel="$seite->titel">
    <x-ui.kopf :titel="$seite->titel" :unter="$seite->hatEntwurf() ? 'Entwurf offen – Besucher sehen noch die veröffentlichte Fassung.' : ($seite->veroeffentlicht_at ? 'Veröffentlicht. Änderungen werden zuerst als Entwurf gespeichert.' : 'Noch nicht veröffentlicht.')">
        @if ($seite->veroeffentlicht_at)
            <x-ui.knopf art="sekundaer" :href="url($seite->slug === 'start' ? '/' : $seite->slug)" target="_blank">Live-Seite ansehen</x-ui.knopf>
        @endif
    </x-ui.kopf>

    <div class="grid gap-6 xl:grid-cols-3"
         x-data="{
            titel: @js($stand['titel']),
            bloecke: @js($stand['bloecke']),
            typen: @js(collect($typen)->map(fn ($t) => ['name' => $t[0], 'standard' => $t[1]])),
            bilder: @js($bilder),
            neuerTyp: 'text',
            hinzufuegen() {
                this.bloecke.push(Object.assign({ typ: this.neuerTyp }, JSON.parse(JSON.stringify(this.typen[this.neuerTyp].standard))));
            },
            verschieben(i, richtung) {
                const ziel = i + richtung;
                if (ziel < 0 || ziel >= this.bloecke.length) return;
                const [b] = this.bloecke.splice(i, 1);
                this.bloecke.splice(ziel, 0, b);
            },
            entfernen(i) { if (confirm('Baustein entfernen?')) this.bloecke.splice(i, 1); },
            umschalten(b, id) { b.bilder = b.bilder.includes(id) ? b.bilder.filter(x => x !== id) : [...b.bilder, id]; },
         }">

        {{-- Bausteine --}}
        <form method="post" action="{{ route('admin.seiten.update', $seite) }}" class="space-y-4 xl:col-span-2">
            @csrf @method('put')
            <input type="hidden" name="bloecke" :value="JSON.stringify(bloecke)">

            <x-ui.karte>
                <label class="mb-1 block text-sm font-medium" for="titel">Seitentitel</label>
                <input id="titel" name="titel" x-model="titel" class="feld" required>
            </x-ui.karte>

            <template x-for="(b, i) in bloecke" :key="i">
                <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <span class="rounded-full bg-marke-50 px-3 py-0.5 text-sm font-medium text-marke-800" x-text="typen[b.typ]?.name ?? b.typ"></span>
                        <div class="flex gap-1">
                            <button type="button" class="rounded px-2 hover:bg-stone-100" @click="verschieben(i, -1)" title="nach oben" aria-label="nach oben">↑</button>
                            <button type="button" class="rounded px-2 hover:bg-stone-100" @click="verschieben(i, 1)" title="nach unten" aria-label="nach unten">↓</button>
                            <button type="button" class="rounded px-2 text-red-700 hover:bg-red-50" @click="entfernen(i)" title="entfernen" aria-label="entfernen">✕</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-if="'titel' in b && b.typ !== 'knopf'">
                            <input x-model="b.titel" class="feld" placeholder="Überschrift (optional)">
                        </template>
                        <template x-if="['text', 'hinweis', 'kopf', 'schichten'].includes(b.typ)">
                            <textarea x-model="b.text" rows="5" class="feld font-mono text-sm" placeholder="Text"></textarea>
                        </template>
                        <template x-if="b.typ === 'hinweis'">
                            <select x-model="b.farbe" class="feld w-48"><option value="orange">orange</option><option value="grau">grau</option></select>
                        </template>
                        <template x-if="b.typ === 'kopf'">
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="b.logo"> Logo-Zeichnung daneben zeigen</label>
                        </template>
                        <template x-if="b.typ === 'liste'">
                            <textarea :value="b.eintraege.join('\n')" @input="b.eintraege = $event.target.value.split('\n')" rows="5" class="feld" placeholder="Ein Punkt pro Zeile"></textarea>
                        </template>
                        <template x-if="b.typ === 'zwei_spalten'">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="space-y-2"><input x-model="b.links_titel" class="feld" placeholder="Überschrift links"><textarea x-model="b.links" rows="6" class="feld font-mono text-sm" placeholder="Text links"></textarea></div>
                                <div class="space-y-2"><input x-model="b.rechts_titel" class="feld" placeholder="Überschrift rechts"><textarea x-model="b.rechts" rows="6" class="feld font-mono text-sm" placeholder="Text rechts"></textarea></div>
                            </div>
                        </template>
                        <template x-if="b.typ === 'faq'">
                            <div class="space-y-3">
                                <template x-for="(e, j) in b.eintraege" :key="j">
                                    <div class="rounded-lg bg-stone-50 p-3">
                                        <div class="flex gap-2"><input x-model="e.frage" class="feld" placeholder="Frage"><button type="button" class="px-2 text-red-700" @click="b.eintraege.splice(j, 1)" aria-label="Frage entfernen">✕</button></div>
                                        <textarea x-model="e.antwort" rows="2" class="feld mt-2" placeholder="Antwort"></textarea>
                                    </div>
                                </template>
                                <button type="button" class="text-sm text-marke-700 hover:underline" @click="b.eintraege.push({ frage: '', antwort: '' })">+ Frage hinzufügen</button>
                            </div>
                        </template>
                        <template x-if="b.typ === 'knopf'">
                            <div class="grid gap-3 md:grid-cols-3">
                                <input x-model="b.text" class="feld" placeholder="Beschriftung">
                                <select x-model="b.ziel" class="feld">
                                    @foreach ($knopfZiele as $wert => $text)<option value="{{ $wert }}">{{ $text }}</option>@endforeach
                                </select>
                                <input x-show="b.ziel === 'url'" x-model="b.url" class="feld" placeholder="https://…">
                            </div>
                        </template>
                        <template x-if="b.typ === 'bild'">
                            <div class="grid gap-3 md:grid-cols-2">
                                <select x-model.number="b.media_id" class="feld">
                                    <option :value="null">– Bild wählen –</option>
                                    <template x-for="bild in bilder" :key="bild.id"><option :value="bild.id" x-text="bild.name" :selected="b.media_id === bild.id"></option></template>
                                </select>
                                <select x-model="b.breite" class="feld"><option value="voll">volle Breite</option><option value="halb">halbe Breite</option></select>
                                <input x-model="b.alt" class="feld md:col-span-2" placeholder="Bildbeschreibung (was ist zu sehen?)">
                                <input x-model="b.beschriftung" class="feld md:col-span-2" placeholder="Bildunterschrift (optional)">
                                <p class="text-xs text-stone-500 md:col-span-2" x-show="!b.media_id && b.pfad">Verwendet das mitgelieferte Bild <span x-text="b.pfad"></span>.</p>
                            </div>
                        </template>
                        <template x-if="b.typ === 'galerie'">
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                <template x-for="bild in bilder" :key="bild.id">
                                    <button type="button" @click="umschalten(b, bild.id)" class="relative overflow-hidden rounded-lg ring-2" :class="b.bilder.includes(bild.id) ? 'ring-marke-500' : 'ring-transparent opacity-50'">
                                        <img :src="bild.url" :alt="bild.alt" class="aspect-square w-full object-cover">
                                    </button>
                                </template>
                                <p x-show="!bilder.length" class="col-span-full text-sm text-stone-500">Noch keine Bilder – rechts hochladen.</p>
                            </div>
                        </template>
                        <template x-if="b.typ === 'termin' || b.typ === 'schichten'">
                            <p class="text-sm text-stone-500">Wird automatisch aus der nächsten Börse gefüllt.</p>
                        </template>
                    </div>
                </div>
            </template>

            <x-ui.karte>
                <div class="flex flex-wrap items-center gap-2">
                    <select x-model="neuerTyp" class="feld w-auto">
                        @foreach ($typen as $typ => [$name, $standard])<option value="{{ $typ }}">{{ $name }}</option>@endforeach
                    </select>
                    <x-ui.knopf type="button" art="sekundaer" x-on:click="hinzufuegen()">+ Baustein hinzufügen</x-ui.knopf>
                </div>
            </x-ui.karte>

            <div class="sticky bottom-0 flex flex-wrap gap-3 rounded-xl border border-stone-200 bg-white/95 p-4 shadow-lg backdrop-blur">
                <x-ui.knopf name="aktion" value="speichern" art="sekundaer">Entwurf speichern</x-ui.knopf>
                <x-ui.knopf name="aktion" value="vorschau" art="sekundaer">Vorschau</x-ui.knopf>
                <x-ui.knopf name="aktion" value="veroeffentlichen" art="erfolg">Veröffentlichen</x-ui.knopf>
            </div>
        </form>

        {{-- Seitenleiste: Bilder, Hilfe, Versionen --}}
        <div class="space-y-6">
            <x-ui.karte titel="Bilder dieser Seite">
                <form method="post" action="{{ route('admin.seiten.bild', $seite) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="file" name="bild" accept="image/*" class="block w-full text-sm" required>
                    <input name="alt" class="feld" placeholder="Was ist zu sehen? (Pflicht)" required>
                    <x-ui.knopf groesse="klein">Hochladen</x-ui.knopf>
                    <p class="text-xs text-stone-500">Große Fotos werden automatisch verkleinert.</p>
                </form>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ($bilder as $bild)
                        <form method="post" action="{{ route('admin.seiten.bild.loeschen', [$seite, $bild['id']]) }}" class="group relative" onsubmit="return confirm('Bild löschen? Bausteine, die es nutzen, zeigen es dann nicht mehr.')">
                            @csrf @method('delete')
                            <img src="{{ $bild['url'] }}" alt="{{ $bild['alt'] }}" title="{{ $bild['alt'] }}" class="aspect-square w-full rounded object-cover">
                            <button class="absolute right-1 top-1 hidden rounded bg-white/90 px-1 text-xs text-red-700 group-hover:block" aria-label="Bild löschen">✕</button>
                        </form>
                    @endforeach
                </div>
            </x-ui.karte>

            <x-ui.karte titel="Hilfe">
                <ul class="space-y-1 text-sm text-stone-600">
                    <li><code>**fett**</code>, <code>- Punkt</code>, <code>1. Schritt</code>, Links als <code>[Text](https://…)</code></li>
                    <li>Platzhalter: <code>{datum}</code>, <code>{verkauf}</code>, <code>{ort}</code>, <code>{anlieferung}</code>, <code>{abholung}</code>, <code>{anmeldung_ab}</code>, <code>{max_teile}</code>, <code>{provision}</code></li>
                    <li>Zeilen, deren Platzhalter leer sind, werden automatisch ausgeblendet.</li>
                </ul>
            </x-ui.karte>

            <x-ui.karte titel="Versionen">
                @if ($seite->hatEntwurf() && $seite->bloecke !== null)
                    <form method="post" action="{{ route('admin.seiten.verwerfen', $seite) }}" class="mb-3" onsubmit="return confirm('Entwurf verwerfen und zur veröffentlichten Fassung zurück?')">
                        @csrf<button class="text-sm text-red-700 hover:underline">Entwurf verwerfen</button>
                    </form>
                @endif
                @forelse ($versionen as $version)
                    <form method="post" action="{{ route('admin.seiten.version', [$seite, $version]) }}" class="flex items-center justify-between border-b border-stone-100 py-1.5 text-sm last:border-0">
                        @csrf
                        <span>{{ $version->created_at->format('d.m.Y H:i') }} <span class="text-stone-500">{{ $version->ersteller?->vorname }}</span></span>
                        <button class="text-marke-700 hover:underline">als Entwurf laden</button>
                    </form>
                @empty
                    <p class="text-sm text-stone-500">Noch keine früheren Fassungen.</p>
                @endforelse
            </x-ui.karte>
        </div>
    </div>
</x-layouts.admin>
