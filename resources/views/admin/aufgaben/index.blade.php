<x-layouts.admin titel="Aufgaben">
    <x-ui.kopf titel="Aufgaben & Checkliste" unter="Zuständige werden {{ \App\Support\Einstellungen::get('erinnerung_aufgaben_tage') }} Tage vor Fälligkeit per Mail erinnert.">
        @if (auth()->user()->istOrga())
            <x-ui.knopf art="sekundaer" :href="route('admin.checklistenvorlagen.index')">Checklisten-Vorlage bearbeiten</x-ui.knopf>
        @endif
    </x-ui.kopf>

    <div class="mb-4 flex flex-wrap gap-2 text-sm">
        @foreach (['boerse' => 'Checkliste '.($boerse?->titel ?? ''), 'team' => 'Allgemeine Team-Aufgaben', 'alle' => 'Alle'] as $wert => $text)
            <a href="{{ request()->fullUrlWithQuery(['ansicht' => $wert]) }}" class="rounded-full px-3 py-1 no-underline {{ $ansicht === $wert ? 'bg-marke-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-300' }}">{{ $text }}</a>
        @endforeach
        <a href="{{ request()->fullUrlWithQuery(['meine' => request()->boolean('meine') ? 0 : 1]) }}" class="rounded-full px-3 py-1 no-underline {{ request()->boolean('meine') ? 'bg-stone-800 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-300' }}">Nur meine</a>
        <a href="{{ request()->fullUrlWithQuery(['erledigte' => request()->boolean('erledigte') ? 0 : 1]) }}" class="rounded-full px-3 py-1 no-underline {{ request()->boolean('erledigte') ? 'bg-stone-800 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-300' }}">Erledigte zeigen</a>
    </div>

    <x-ui.karte>
        @forelse ($aufgaben as $aufgabe)
            <div class="border-b border-stone-100 py-3 last:border-0" x-data="{ bearbeiten: false }">
                <div class="flex items-start gap-3">
                    <form method="post" action="{{ route('admin.aufgaben.erledigt', $aufgabe) }}">
                        @csrf
                        <button class="mt-0.5 flex h-6 w-6 items-center justify-center rounded border {{ $aufgabe->erledigt_at ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-stone-400 hover:bg-emerald-50' }}" aria-label="Erledigt umschalten">{{ $aufgabe->erledigt_at ? '✓' : '' }}</button>
                    </form>
                    <div class="flex-1">
                        <p class="{{ $aufgabe->erledigt_at ? 'text-stone-400 line-through' : ($aufgabe->istUeberfaellig() ? 'font-medium text-red-800' : 'font-medium') }}">{{ $aufgabe->titel }}</p>
                        @if ($aufgabe->beschreibung)<p class="whitespace-pre-line text-sm text-stone-600">{{ $aufgabe->beschreibung }}</p>@endif
                        <p class="text-xs text-stone-500">
                            {{ collect([
                                $aufgabe->phase,
                                $aufgabe->faellig_am ? 'fällig '.$aufgabe->faellig_am->format('d.m.Y') : null,
                                $aufgabe->zustaendig ? 'zuständig: '.$aufgabe->zustaendig->name : 'noch niemand zuständig',
                                $ansicht === 'alle' ? ($aufgabe->boerse?->titel ?? 'Team') : null,
                                $aufgabe->erledigt_at ? 'erledigt von '.$aufgabe->erledigtVon?->name.' am '.$aufgabe->erledigt_at->format('d.m.Y') : null,
                            ])->filter()->implode(' · ') }}
                        </p>
                    </div>
                    <button type="button" class="text-sm text-marke-700 hover:underline" @click="bearbeiten = !bearbeiten">Bearbeiten</button>
                </div>
                <form x-show="bearbeiten" x-cloak method="post" action="{{ route('admin.aufgaben.update', $aufgabe) }}" class="mt-3 grid gap-2 pl-9 md:grid-cols-4">
                    @csrf @method('put')
                    <input name="titel" value="{{ $aufgabe->titel }}" class="feld md:col-span-2" required>
                    <input name="faellig_am" type="date" value="{{ $aufgabe->faellig_am?->format('Y-m-d') }}" class="feld">
                    <select name="zustaendig_id" class="feld">
                        <option value="">niemand</option>
                        @foreach ($team as $p)<option value="{{ $p->id }}" @selected($aufgabe->zustaendig_id === $p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                    <input type="hidden" name="phase" value="{{ $aufgabe->phase }}">
                    <textarea name="beschreibung" rows="2" class="feld md:col-span-4" placeholder="Beschreibung">{{ $aufgabe->beschreibung }}</textarea>
                    <div class="flex gap-3 md:col-span-4">
                        <x-ui.knopf groesse="klein">Speichern</x-ui.knopf>
                    </div>
                </form>
                <form x-show="bearbeiten" x-cloak method="post" action="{{ route('admin.aufgaben.destroy', $aufgabe) }}" class="mt-2 pl-9" onsubmit="return confirm('Aufgabe löschen?')">
                    @csrf @method('delete')<button class="text-xs text-red-700 hover:underline">Aufgabe löschen</button>
                </form>
            </div>
        @empty
            <p class="text-stone-500">Keine offenen Aufgaben.</p>
        @endforelse
    </x-ui.karte>

    <x-ui.karte titel="Neue Aufgabe" class="mt-6">
        <form method="post" action="{{ route('admin.aufgaben.store') }}" class="grid gap-3 md:grid-cols-4 md:items-end">
            @csrf
            <x-ui.feld name="titel" label="Was ist zu tun?" class="md:col-span-2" required />
            <x-ui.feld name="faellig_am" label="Fällig am" typ="date" />
            <x-ui.auswahl name="zustaendig_id" label="Zuständig" leer="niemand" :optionen="$team->pluck('name', 'id')" />
            <x-ui.feld name="beschreibung" label="Beschreibung (optional)" typ="textarea" class="md:col-span-4" />
            <label class="flex items-center gap-2 text-sm md:col-span-3"><input type="checkbox" name="fuer_boerse" value="1" @checked($ansicht !== 'team')> Gehört zur Checkliste von {{ $boerse?->titel }}</label>
            <x-ui.knopf>Anlegen</x-ui.knopf>
        </form>
    </x-ui.karte>
</x-layouts.admin>
