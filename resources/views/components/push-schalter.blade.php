@props(['text' => 'Erinnerungen (z. B. an deine Schicht oder die Kistenabgabe) zusätzlich als Nachricht aufs Handy.'])
@php
    $pushDaten = [
        'schluessel' => \App\Domain\Push\Push::oeffentlicherSchluessel(),
        'urls' => ['speichern' => route('push.speichern'), 'loeschen' => route('push.loeschen'), 'test' => route('push.test')],
    ];
@endphp
<div {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-4']) }} x-data="pushSchalter(@js($pushDaten))">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="font-medium">🔔 Push-Nachrichten auf diesem Gerät</p>
            <p class="text-sm text-stone-600">{{ $text }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <template x-if="zustand === 'aus'">
                <button type="button" class="rounded-lg bg-marke-600 px-4 py-2 font-medium text-white disabled:opacity-50" :disabled="laeuft" @click="einschalten()">Einschalten</button>
            </template>
            <template x-if="zustand === 'an'">
                <span class="flex gap-2">
                    <button type="button" class="rounded-lg border border-stone-300 px-3 py-2 text-sm" @click="testen()">Testnachricht senden</button>
                    <button type="button" class="rounded-lg border border-stone-300 px-3 py-2 text-sm disabled:opacity-50" :disabled="laeuft" @click="ausschalten()">Ausschalten</button>
                </span>
            </template>
        </div>
    </div>
    <p class="mt-2 text-sm text-emerald-700" x-show="zustand === 'an' && !meldung" x-cloak>✓ Eingeschaltet</p>
    <p class="mt-2 text-sm text-stone-600" x-show="zustand === 'ios_installieren'" x-cloak>
        Auf dem iPhone: unten auf „Teilen“ tippen → „Zum Home-Bildschirm“. Danach die Seite über das neue Symbol öffnen und hier einschalten.
    </p>
    <p class="mt-2 text-sm text-stone-600" x-show="zustand === 'nicht_moeglich'" x-cloak>Dieser Browser unterstützt keine Push-Nachrichten. Du bekommst alles weiterhin per E-Mail.</p>
    <p class="mt-2 text-sm text-amber-800" x-show="zustand === 'blockiert'" x-cloak>Benachrichtigungen sind für diese Seite blockiert. Du kannst sie in den Browser-Einstellungen wieder erlauben.</p>
    <p class="mt-2 text-sm" x-show="meldung" x-cloak x-text="meldung"></p>
</div>
