<x-layouts.admin :titel="$protokoll->titel">
    <x-ui.kopf :titel="$protokoll->titel" :unter="$protokoll->datum->isoFormat('dddd, D. MMMM YYYY').($protokoll->boerse ? ' · '.$protokoll->boerse->titel : '').($protokoll->autor ? ' · geschrieben von '.$protokoll->autor->name : '')">
        <x-ui.knopf art="sekundaer" :href="route('admin.protokolle.edit', $protokoll)">Bearbeiten</x-ui.knopf>
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.karte class="lg:col-span-2">
            @if ($protokoll->teilnehmende)<p class="mb-4 text-sm text-stone-600">Dabei: {{ $protokoll->teilnehmende }}</p>@endif
            <div class="inhalt">{!! $protokoll->html() !!}</div>
        </x-ui.karte>

        <div class="space-y-6">
            @if ($protokoll->beschluesse())
                <x-ui.karte titel="Beschlüsse">
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        @foreach ($protokoll->beschluesse() as $b)<li>{{ $b }}</li>@endforeach
                    </ul>
                </x-ui.karte>
            @endif

            <x-ui.karte titel="Offene Punkte → Aufgaben">
                @forelse ($protokoll->offenePunkte() as $zeile => $punkt)
                    <form method="post" action="{{ route('admin.protokolle.aufgabe', $protokoll) }}" class="border-b border-stone-100 py-2 last:border-0">
                        @csrf
                        <input type="hidden" name="zeile" value="{{ $zeile }}">
                        <p class="text-sm">{{ $punkt }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="date" name="faellig_am" class="feld w-40 py-1 text-sm" aria-label="Fällig am">
                            <button class="text-sm text-marke-700 hover:underline">Als Aufgabe anlegen</button>
                        </div>
                    </form>
                @empty
                    <p class="text-sm text-stone-500">Keine offenen Punkte. Punkte mit „- [ ] “ im Text erscheinen hier.</p>
                @endforelse
            </x-ui.karte>

            <form method="post" action="{{ route('admin.protokolle.destroy', $protokoll) }}" onsubmit="return confirm('Protokoll löschen?')">
                @csrf @method('delete')<button class="text-sm text-red-700 hover:underline">Protokoll löschen</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
