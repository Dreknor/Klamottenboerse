<x-layouts.oeffentlich titel="Feedback">
    <div class="mx-auto max-w-xl">
        <h1>Wie war die Klamottenbörse?</h1>
        <p class="mt-2 text-stone-600">{{ $feedback->boerse->titel }} · dauert nur eine Minute</p>

        <form method="post" class="mt-6 space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            @csrf
            @foreach ($fragen as $frage)
                @php
                    $name = "antworten[{$frage->id}]";
                    $wert = old("antworten.{$frage->id}", $antworten[$frage->id]?->zahl ?? $antworten[$frage->id]?->text ?? null);
                    $titel = $loop->iteration.'. '.$frage->text.($frage->pflicht ? ' *' : '');
                @endphp
                @if ($frage->typ === 'sterne')
                    <fieldset x-data="{ sterne: {{ (int) $wert }} }">
                        <legend class="mb-2 font-medium">{{ $titel }}</legend>
                        <input type="hidden" name="{{ $name }}" :value="sterne || ''">
                        <div class="flex gap-1 text-4xl">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="sterne = {{ $i }}" :class="sterne >= {{ $i }} ? 'text-amber-400' : 'text-stone-300'" aria-label="{{ $i }} von 5 Sternen">★</button>
                            @endfor
                        </div>
                        @error("antworten.{$frage->id}")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </fieldset>
                @elseif ($frage->typ === 'auswahl')
                    <fieldset>
                        <legend class="mb-2 font-medium">{{ $titel }}</legend>
                        <div class="space-y-1">
                            @foreach ($frage->optionen ?? [] as $option)
                                <label class="flex items-center gap-2"><input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($wert === $option)> {{ $option }}</label>
                            @endforeach
                        </div>
                        @error("antworten.{$frage->id}")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </fieldset>
                @else
                    <div>
                        <label for="frage-{{ $frage->id }}" class="mb-1 block font-medium">{{ $titel }}</label>
                        <textarea id="frage-{{ $frage->id }}" name="{{ $name }}" rows="4" class="feld" @required($frage->pflicht)>{{ $wert }}</textarea>
                        @error("antworten.{$frage->id}")<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endif
            @endforeach
            @if ($fragen->contains('pflicht', true))<p class="text-xs text-stone-500">* bitte ausfüllen</p>@endif
            <x-ui.knopf class="w-full">Absenden</x-ui.knopf>
        </form>
    </div>
</x-layouts.oeffentlich>
