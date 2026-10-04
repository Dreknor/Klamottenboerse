{{-- Suchfeld mit Handscanner- und Kamera-Unterstützung --}}
<form method="get" class="rounded-2xl bg-white p-4 shadow-sm" x-data="scanner({ ziel: '#suche' })">
    <label for="suche" class="mb-1 block font-medium">{{ $label ?? 'Nummer oder Name' }}</label>
    <div class="flex gap-2">
        <input id="suche" name="{{ $name ?? 'suche' }}" value="{{ request($name ?? 'suche') }}" class="feld text-2xl" autofocus autocomplete="off" inputmode="{{ $inputmode ?? 'text' }}">
        <button class="rounded-lg bg-marke-600 px-5 text-white">Suchen</button>
        <button type="button" x-show="verfuegbar" @click="aktiv ? stoppen() : starten()" class="rounded-lg bg-stone-800 px-4 text-white" aria-label="Mit Kamera scannen">📷</button>
    </div>
    <video x-ref="video" x-show="aktiv" x-cloak class="mt-3 w-full rounded-lg" playsinline muted></video>
</form>
