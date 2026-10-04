<x-layouts.tablet :titel="'Kasse · '.$boerse->titel" :zurueck="auth()->user()->istOrga() ? route('admin.dashboard') : null">
    @if ($boerse->status === \App\Enums\BoerseStatus::Abgeschlossen)
        <div class="mb-4 rounded-xl bg-red-600 px-4 py-3 font-medium text-white">Diese Börse ist abgeschlossen – hier kann nicht mehr kassiert werden.</div>
    @endif
    <div x-data="kasse({ datenUrl: '{{ route('kasse.daten') }}', syncUrl: '{{ route('kasse.sync') }}', stornoUrl: '{{ route('kasse.storno', '__UUID__') }}', boerseId: {{ $boerse->id }} })" class="grid gap-4 lg:grid-cols-5">

        {{-- Statusleiste --}}
        <div class="flex flex-wrap items-center gap-3 text-sm lg:col-span-5">
            <span class="rounded-full px-3 py-1" :class="online ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'" x-text="online ? 'Online' : 'Offline – Verkauf läuft trotzdem weiter'"></span>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-900" x-show="offeneBons.length" x-cloak x-text="offeneBons.length + ' Bon(s) noch nicht übertragen'"></span>
            <label class="ml-auto flex items-center gap-2">Kasse:
                <input class="feld w-32 py-1" x-model="kassenname" @change="localStorage.setItem('kasse.name', kassenname)" placeholder="Kasse 1">
            </label>
        </div>

        <div class="space-y-4 lg:col-span-3">
            <div x-show="meldung" x-cloak class="rounded-xl px-4 py-3 text-base font-medium"
                 :class="meldung?.art === 'fehler' ? 'bg-red-600 text-white' : 'bg-amber-300 text-amber-950'" x-text="meldung?.text"></div>

            <template x-if="modus === 'erfassen'">
                <div class="space-y-4">
                    <div class="rounded-2xl bg-white p-4 shadow-sm" x-data="scanner({ ziel: '#scan-feld' })">
                        <label for="scan-feld" class="mb-1 block font-medium">Etikett scannen</label>
                        <div class="flex gap-2">
                            <input id="scan-feld" x-ref="scan" x-model="eingabe" @keydown.enter.prevent="scannen()" @input="eingabe.length === 11 && /^\d+$/.test(eingabe) && scannen()"
                                   class="feld text-2xl" inputmode="numeric" autocomplete="off" placeholder="Barcode">
                            <button type="button" x-show="verfuegbar" @click="aktiv ? stoppen() : starten()" class="rounded-lg bg-stone-800 px-4 text-white">📷</button>
                        </div>
                        <video x-ref="video" x-show="aktiv" x-cloak class="mt-3 w-full rounded-lg" playsinline muted></video>
                    </div>

                    <form class="rounded-2xl bg-white p-4 shadow-sm" @submit.prevent="manuell()">
                        <p class="mb-2 font-medium">Handschriftliches Etikett</p>
                        <div class="grid grid-cols-3 gap-2">
                            <input x-ref="nummer" x-model="nummer" class="feld text-2xl" inputmode="numeric" placeholder="Nummer" aria-label="Verkäufernummer">
                            <input x-model="artikel" class="feld text-2xl" inputmode="numeric" placeholder="Artikel" aria-label="Artikelnummer">
                            <input x-model="preis" class="feld text-2xl" inputmode="decimal" placeholder="Preis" aria-label="Preis in Euro">
                        </div>
                        <button class="mt-3 w-full rounded-xl bg-stone-800 py-3 text-lg font-medium text-white">Hinzufügen</button>
                    </form>
                </div>
            </template>

            <template x-if="modus === 'bezahlen'">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-lg">Zu zahlen</p>
                    <p class="text-5xl font-bold" x-text="euro(summe)"></p>
                    <label class="mt-6 block font-medium" for="gegeben">Gegeben</label>
                    <input id="gegeben" x-ref="gegeben" x-model="gegeben" @keydown.enter.prevent="abschliessen()" class="feld text-3xl" inputmode="decimal" placeholder="z. B. 50">
                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="schein in [5, 10, 20, 50, 100]">
                            <button type="button" class="rounded-lg bg-stone-200 px-4 py-2 text-lg" @click="gegeben = String(schein)" x-text="schein + ' €'"></button>
                        </template>
                    </div>
                    <p class="mt-6 text-lg" x-show="wechselgeld !== null">Wechselgeld</p>
                    <p class="text-5xl font-bold" :class="wechselgeld < 0 ? 'text-red-600' : 'text-emerald-700'" x-show="wechselgeld !== null" x-text="euro(wechselgeld ?? 0)"></p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="button" class="rounded-xl bg-stone-200 py-4 text-lg" @click="modus = 'erfassen'">Zurück</button>
                        <button type="button" class="rounded-xl bg-emerald-600 py-4 text-lg font-semibold text-white" @click="abschliessen()">Bezahlt ✓</button>
                    </div>
                </div>
            </template>

            <p x-show="letzterBon" x-cloak class="text-stone-600">
                Letzter Einkauf: <span x-text="euro(letzterBon?.summe ?? 0)"></span>,
                Wechselgeld <span x-text="euro(letzterBon?.wechselgeld ?? 0)"></span>
                · <button type="button" class="text-red-700 underline" @click="stornieren()">stornieren</button>
            </p>
        </div>

        {{-- Aktueller Bon --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm lg:col-span-2">
            <div class="flex items-baseline justify-between">
                <p class="font-medium">Aktueller Einkauf</p>
                <p class="text-sm text-stone-500" x-text="positionen.length + ' Teile'"></p>
            </div>
            <ul class="mt-2 max-h-[45vh] divide-y divide-stone-100 overflow-y-auto">
                <template x-for="(p, i) in positionen" :key="p.nummer + '-' + p.artikel">
                    <li class="flex items-center justify-between py-2">
                        <span class="font-mono" x-text="p.nummer + '-' + p.artikel"></span>
                        <span class="flex items-center gap-3">
                            <span x-text="euro(p.preis_cent)"></span>
                            <button type="button" class="rounded bg-red-50 px-2 text-red-700" @click="entfernen(i)" aria-label="Entfernen">✕</button>
                        </span>
                    </li>
                </template>
            </ul>
            <div class="mt-3 flex items-baseline justify-between border-t border-stone-200 pt-3">
                <span class="text-lg">Summe</span>
                <span class="text-3xl font-bold" x-text="euro(summe)"></span>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2" x-show="modus === 'erfassen'">
                <button type="button" class="rounded-xl bg-stone-200 py-4" @click="abbrechen()">Verwerfen</button>
                <button type="button" class="col-span-2 rounded-xl bg-marke-600 py-4 text-lg font-semibold text-white disabled:opacity-40" :disabled="!positionen.length" @click="zurBezahlung()">Bezahlen</button>
            </div>
        </div>
    </div>
</x-layouts.tablet>
