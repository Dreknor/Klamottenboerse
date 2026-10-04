<x-layouts.oeffentlich titel="Helfen">
    <h1>Du willst helfen? Super!</h1>
    <p class="mt-2 text-stone-600">Die Klamottenbörse funktioniert nur mit vielen helfenden Händen. Such dir eine Schicht aus und trag dich ein.</p>

    @forelse ($schichten as $tag => $liste)
        <h2 class="mb-3 mt-8">{{ $tag }}</h2>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($liste as $schicht)
                @php $frei = max(0, $schicht->soll - $schicht->zusagen_count); @endphp
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200" x-data="{ offen: {{ old('schicht_id') == $schicht->id ? 'true' : 'false' }} }">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $schicht->bereich }}</p>
                            <p class="text-stone-600">{{ $schicht->beginn->format('H:i') }}–{{ $schicht->ende->format('H:i') }} Uhr</p>
                            @if ($schicht->beschreibung)<p class="text-sm text-stone-500">{{ $schicht->beschreibung }}</p>@endif
                        </div>
                        @if ($frei > 0)
                            <x-ui.abzeichen farbe="emerald">{{ $frei }} frei</x-ui.abzeichen>
                        @else
                            <x-ui.abzeichen>voll</x-ui.abzeichen>
                        @endif
                    </div>
                    @if ($frei > 0)
                        <button type="button" class="mt-3 font-medium text-marke-700" @click="offen = !offen" x-show="!offen">Ich helfe hier →</button>
                        <form x-show="offen" x-cloak method="post" action="{{ route('helfer.eintragen', $schicht) }}" class="mt-3 grid grid-cols-2 gap-2">
                            @csrf
                            <input type="hidden" name="schicht_id" value="{{ $schicht->id }}">
                            <div class="hidden" aria-hidden="true"><input name="webseite" tabindex="-1" autocomplete="off"></div>
                            <input name="vorname" class="feld" placeholder="Vorname" value="{{ old('vorname') }}" required autocomplete="given-name">
                            <input name="nachname" class="feld" placeholder="Nachname" value="{{ old('nachname') }}" required autocomplete="family-name">
                            <input name="email" type="email" class="feld col-span-2" placeholder="E-Mail" value="{{ old('email') }}" required autocomplete="email">
                            <input name="telefon" type="tel" class="feld col-span-2" placeholder="Telefon (optional)" value="{{ old('telefon') }}" autocomplete="tel">
                            <div class="col-span-2"><x-ui.knopf class="w-full">Eintragen</x-ui.knopf></div>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <p class="mt-6 rounded-xl bg-white p-6 ring-1 ring-stone-200">Für die nächste Börse sind noch keine Schichten geplant.</p>
    @endforelse
</x-layouts.oeffentlich>
