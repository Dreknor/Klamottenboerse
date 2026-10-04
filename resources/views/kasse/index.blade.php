@php
    $urls = [
        'daten' => route('kasse.daten'),
        'warenkorb' => route('kasse.warenkorb'),
        'abschliessen' => route('kasse.warenkorb.abschliessen'),
        'sync' => route('kasse.sync'),
        'storno' => route('kasse.storno', '__UUID__'),
        'token' => route('kasse.token'),
    ];
@endphp
<x-layouts.tablet :titel="'Kasse · '.$boerse->titel" :zurueck="auth()->user()->istOrga() ? route('admin.dashboard') : null">
    @if ($boerse->status === \App\Enums\BoerseStatus::Abgeschlossen)
        <div class="mb-4 rounded-xl bg-red-600 px-4 py-3 font-medium text-white">Diese Börse ist abgeschlossen – hier kann nicht mehr kassiert werden.</div>
    @endif

    <div x-data="kasse({ urls: @js($urls), boerseId: {{ $boerse->id }} })" class="grid gap-4 lg:grid-cols-5">

        {{-- Statusleiste --}}
        <div class="flex flex-wrap items-center gap-3 text-sm lg:col-span-5">
            <span class="rounded-full px-3 py-1" :class="online ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'" x-text="online ? 'Online · Warenkorb wird mit deinen anderen Geräten abgeglichen' : 'Offline – Verkauf läuft trotzdem weiter'"></span>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-900" x-show="ungesendet" x-cloak x-text="ungesendet + ' Änderung(en) noch nicht übertragen'"></span>
            <label class="ml-auto flex items-center gap-2">Kasse:
                <input class="feld w-32 py-1" x-model="kassenname" @change="localStorage.setItem('kasse.name', kassenname)" placeholder="Kasse 1">
            </label>
        </div>

        <div class="space-y-4 lg:col-span-3">
            <div x-show="meldung" x-cloak class="rounded-xl px-4 py-3 text-base font-medium" role="alert"
                 :class="meldung?.art === 'fehler' ? 'bg-red-600 text-white' : 'bg-amber-300 text-amber-950'" x-text="meldung?.text"></div>

            <div x-show="modus === 'erfassen'" class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <p class="font-medium">Artikel erfassen</p>
                    <button type="button" class="rounded-lg px-4 py-2 text-white" :class="kamera ? 'bg-red-600' : 'bg-stone-800'" @click="kameraStarten()" x-text="kamera ? 'Kamera aus' : '📷 Mit Kamera scannen'"></button>
                </div>
                <video x-ref="video" x-show="kamera" x-cloak class="mb-3 max-h-72 w-full rounded-lg bg-black object-cover" playsinline muted></video>

                <form @submit.prevent="handAbsenden()" class="grid grid-cols-3 gap-2" autocomplete="off">
                    <div>
                        <label for="k-nummer" class="text-xs text-stone-500">Nummer / QR</label>
                        <input id="k-nummer" x-ref="nummer" x-model="nummer" @input="nummerEingabe()" @keydown.enter.prevent="nummerEnter()"
                               class="feld text-2xl" inputmode="numeric" placeholder="215">
                    </div>
                    <div>
                        <label for="k-artikel" class="text-xs text-stone-500">Artikel</label>
                        <input id="k-artikel" x-ref="artikel" x-model="artikel" @keydown.enter.prevent="artikelEnter()"
                               class="feld text-2xl" inputmode="numeric" placeholder="7">
                    </div>
                    <div>
                        <label for="k-preis" class="text-xs text-stone-500">Preis €</label>
                        <input id="k-preis" x-ref="preis" x-model="preis" @keydown.enter.prevent="preisEnter()"
                               class="feld text-2xl" inputmode="decimal" placeholder="4,50">
                    </div>
                    <button class="col-span-3 mt-1 rounded-xl bg-stone-800 py-3 text-lg font-medium text-white">Hinzufügen (Enter)</button>
                </form>
                <p class="mt-2 text-xs text-stone-500">Enter oder Tab springt ins nächste Feld, Enter im Preisfeld fügt hinzu. Ein Handscanner kann direkt ins Nummernfeld scannen.</p>
            </div>

            <div x-show="modus === 'bezahlen'" x-cloak class="rounded-2xl bg-white p-6 shadow-sm">
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
                    <button type="button" class="rounded-xl bg-stone-200 py-4 text-lg" @click="modus = 'erfassen'; $nextTick(() => $refs.nummer.focus())">Zurück</button>
                    <button type="button" class="rounded-xl bg-emerald-600 py-4 text-lg font-semibold text-white" @click="abschliessen()">Bezahlt ✓</button>
                </div>
            </div>

            <p x-show="letzterBon" x-cloak class="text-stone-600">
                Letzter Einkauf <span x-text="letzterBon?.um"></span>: <strong x-text="euro(letzterBon?.summe_cent ?? 0)"></strong>
                <template x-if="letzterBon?.wechselgeld !== null && letzterBon?.wechselgeld !== undefined">
                    <span>, Wechselgeld <span x-text="euro(letzterBon.wechselgeld)"></span></span>
                </template>
                · <button type="button" class="text-red-700 underline" @click="stornieren()">stornieren</button>
            </p>
        </div>

        {{-- Aktueller Einkauf (auf allen Geräten des Kontos gleich) --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm lg:col-span-2">
            <div class="flex items-baseline justify-between">
                <p class="font-medium">Aktueller Einkauf</p>
                <p class="text-sm text-stone-500" x-text="positionen.length + ' Teile'"></p>
            </div>
            <ul class="mt-2 max-h-[45vh] divide-y divide-stone-100 overflow-y-auto">
                <template x-for="p in positionen" :key="p.uuid">
                    <li class="flex items-center justify-between py-2" :class="p.wartet && 'opacity-60'">
                        <span class="font-mono" x-text="p.nummer + '-' + p.artikel"></span>
                        <span class="flex items-center gap-3">
                            <span x-text="euro(p.preis_cent)"></span>
                            <button type="button" class="rounded bg-red-50 px-2 text-red-700" @click="entfernen(p.uuid)" aria-label="Entfernen">✕</button>
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
