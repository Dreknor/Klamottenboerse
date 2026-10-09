@php use App\Models\FeedbackFrage; @endphp
<x-layouts.admin titel="Feedback-Fragen">
    <x-ui.kopf titel="Feedback-Fragen" unter="Diese Fragen bekommen Verkäufer und Helfer nach der Börse über den persönlichen Link aus der Feedback-Mail. Änderungen gelten sofort." />

    <x-ui.karte titel="Fragen in der Umfrage">
        <div class="space-y-4">
            @foreach ($fragen as $f)
                <form method="post" action="{{ route('admin.feedback.fragen.update', $f) }}" x-data="{ typ: @js($f->typ) }"
                      class="grid gap-3 rounded-lg border border-stone-200 p-4 md:grid-cols-12 md:items-start {{ $f->aktiv ? '' : 'bg-stone-50 text-stone-500' }}">
                    @csrf @method('put')
                    <div class="md:col-span-1">
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="sortierung-{{ $f->id }}">Reihe</label>
                        <input id="sortierung-{{ $f->id }}" name="sortierung" type="number" min="0" value="{{ $f->sortierung }}" class="feld py-1">
                    </div>
                    <div class="md:col-span-5">
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="text-{{ $f->id }}">Frage</label>
                        <input id="text-{{ $f->id }}" name="text" value="{{ $f->text }}" class="feld py-1" required maxlength="255">
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="typ-{{ $f->id }}">Typ</label>
                        @if ($f->antworten_count > 0)
                            {{-- Typ steht fest, sobald es Antworten gibt --}}
                            <input type="hidden" name="typ" value="{{ $f->typ }}">
                            <p id="typ-{{ $f->id }}" class="py-1">{{ FeedbackFrage::TYPEN[$f->typ] }}</p>
                        @else
                            <select id="typ-{{ $f->id }}" name="typ" x-model="typ" class="feld py-1">
                                @foreach (FeedbackFrage::TYPEN as $wert => $text)<option value="{{ $wert }}" @selected($f->typ === $wert)>{{ $text }}</option>@endforeach
                            </select>
                        @endif
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="rolle-{{ $f->id }}">Für</label>
                        <select id="rolle-{{ $f->id }}" name="rolle" class="feld py-1">
                            @foreach (FeedbackFrage::ROLLEN as $wert => $text)<option value="{{ $wert }}" @selected((string) $f->rolle === $wert)>{{ $text }}</option>@endforeach
                        </select>
                    </div>
                    <div class="md:col-span-6 md:col-start-2" x-show="typ === 'auswahl'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="optionen-{{ $f->id }}">Antwortmöglichkeiten <span class="font-normal text-stone-500">(eine pro Zeile)</span></label>
                        <textarea id="optionen-{{ $f->id }}" name="optionen" rows="3" class="feld py-1" :disabled="typ !== 'auswahl'">{{ implode("\n", $f->optionen ?? []) }}</textarea>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm md:col-span-11 md:col-start-2">
                        <label class="flex items-center gap-2"><input type="checkbox" name="pflicht" value="1" @checked($f->pflicht)> Pflichtfrage</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="aktiv" value="1" @checked($f->aktiv)> in der Umfrage</label>
                        <span class="text-stone-500">{{ $f->antworten_count }} Antworten bisher</span>
                        <span class="ml-auto flex gap-2">
                            <x-ui.knopf groesse="klein" art="sekundaer">Speichern</x-ui.knopf>
                            <x-ui.knopf groesse="klein" art="leise" form="loeschen-{{ $f->id }}">Löschen</x-ui.knopf>
                        </span>
                    </div>
                </form>
                <form id="loeschen-{{ $f->id }}" method="post" action="{{ route('admin.feedback.fragen.destroy', $f) }}" class="hidden"
                      onsubmit="return confirm(@js('Frage „'.$f->text.'“ löschen?'))">@csrf @method('delete')</form>
            @endforeach
        </div>
        @error('optionen')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
        <p class="mt-3 text-xs text-stone-500">Fragen mit Antworten lassen sich nicht löschen und ihr Typ bleibt fest – nimm sie stattdessen aus der Umfrage, dann bleibt die Auswertung früherer Börsen erhalten. Die erste Sterne-Frage gilt als Gesamtnote in der Statistik.</p>
    </x-ui.karte>

    <x-ui.karte titel="Neue Frage" class="mt-6">
        <form method="post" action="{{ route('admin.feedback.fragen.store') }}" x-data="{ typ: @js(old('typ', 'text')) }" class="grid gap-3 md:grid-cols-12 md:items-start">
            @csrf
            <x-ui.feld name="text" label="Frage" class="md:col-span-6" required maxlength="255" placeholder="z. B. Wie bist du auf die Börse aufmerksam geworden?" />
            <div class="md:col-span-3">
                <label for="typ" class="mb-1 block text-sm font-medium text-stone-700">Typ</label>
                <select id="typ" name="typ" x-model="typ" class="feld">
                    @foreach (FeedbackFrage::TYPEN as $wert => $text)<option value="{{ $wert }}">{{ $text }}</option>@endforeach
                </select>
            </div>
            <x-ui.auswahl name="rolle" label="Für" :optionen="FeedbackFrage::ROLLEN" class="md:col-span-3" />
            <div class="md:col-span-6" x-show="typ === 'auswahl'" x-cloak>
                <label for="optionen" class="mb-1 block text-sm font-medium text-stone-700">Antwortmöglichkeiten <span class="font-normal text-stone-500">(eine pro Zeile)</span></label>
                <textarea id="optionen" name="optionen" rows="3" class="feld" :disabled="typ !== 'auswahl'" placeholder="Zeitung&#10;Freunde&#10;Aushang">{{ old('optionen') }}</textarea>
            </div>
            <div class="flex items-center gap-4 md:col-span-12">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="pflicht" value="1" @checked(old('pflicht'))> Pflichtfrage</label>
                <x-ui.knopf>Anlegen</x-ui.knopf>
            </div>
        </form>
    </x-ui.karte>
</x-layouts.admin>
