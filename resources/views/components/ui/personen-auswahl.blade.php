@props([
    'name' => 'person_id',
    'label' => 'Person',
    'hilfe' => null,
    'springen' => false,      // true: Treffer öffnet die Person (Schnellsuche)
    'angemeldetSperren' => false, // true: wer bei der aktuellen Börse schon dabei ist, ist nicht wählbar
    'platzhalter' => 'Name, E-Mail, Telefon oder Nummer tippen …',
])
@php $id = 'ps-'.\Illuminate\Support\Str::random(6); @endphp
<div {{ $attributes->merge(['class' => 'relative']) }}
     x-data="personenSuche({ url: @js(route('admin.personen.suche')), springen: @js($springen), sperren: @js($angemeldetSperren) })"
     @click.outside="offen = false" @keydown.escape="offen = false">
    @if ($label)<label for="{{ $id }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>@endif

    @unless ($springen)
        <input type="hidden" name="{{ $name }}" :value="gewaehlt?.id ?? ''">
        <div x-show="gewaehlt" x-cloak class="flex items-center justify-between gap-2 rounded-lg border border-marke-300 bg-marke-50 px-3 py-2">
            <span><strong x-text="gewaehlt?.name"></strong> <span class="text-sm text-stone-600" x-text="gewaehlt?.email"></span></span>
            <button type="button" class="text-sm text-marke-700 hover:underline" @click="zuruecksetzen()">ändern</button>
        </div>
    @endunless

    <div @unless ($springen) x-show="!gewaehlt" @endunless class="relative">
        <input id="{{ $id }}" x-ref="eingabe" x-model="q" @input="eingabe()" @focus="treffer.length && (offen = true)"
               @keydown.arrow-down.prevent="runter()" @keydown.arrow-up.prevent="hoch()" @keydown.enter.prevent="enter()"
               type="search" autocomplete="off" class="feld" placeholder="{{ $platzhalter }}"
               role="combobox" :aria-expanded="offen" aria-controls="{{ $id }}-liste">
        <span x-show="laedt" x-cloak class="absolute right-3 top-2.5 text-xs text-stone-400">…</span>
    </div>

    <ul id="{{ $id }}-liste" x-show="offen" x-cloak role="listbox"
        class="absolute z-40 mt-1 max-h-80 w-full min-w-64 overflow-y-auto rounded-lg border border-stone-200 bg-white text-sm text-stone-800 shadow-lg">
        <template x-for="(p, i) in treffer" :key="p.id">
            <li role="option" :aria-selected="i === markiert" @mouseenter="markiert = i"
                @click="waehlen(p)"
                class="flex cursor-pointer items-center justify-between gap-2 px-3 py-2"
                :class="[i === markiert ? 'bg-marke-50' : '', {{ $angemeldetSperren ? 'p.angemeldet' : 'false' }} ? 'cursor-not-allowed opacity-50' : '']">
                <span class="min-w-0">
                    <span class="block truncate font-medium" x-text="p.name"></span>
                    <span class="block truncate text-xs text-stone-500" x-text="[p.email, p.telefon].filter(Boolean).join(' · ')"></span>
                </span>
                <span class="shrink-0 font-mono text-xs" x-show="p.angemeldet" x-text="p.nummer ? 'Nr. ' + p.nummer : 'Warteliste'"></span>
            </li>
        </template>
        <li x-show="!treffer.length" class="px-3 py-2 text-stone-500">Niemand gefunden.</li>
    </ul>
    @if ($hilfe)<p class="mt-1 text-xs text-stone-500">{{ $hilfe }}</p>@endif
    @error($name)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
