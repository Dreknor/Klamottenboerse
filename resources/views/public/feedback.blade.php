<x-layouts.oeffentlich titel="Feedback">
    <div class="mx-auto max-w-xl">
        <h1>Wie war die Klamottenbörse?</h1>
        <p class="mt-2 text-stone-600">{{ $feedback->boerse->titel }} · dauert nur eine Minute</p>

        <form method="post" class="mt-6 space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200" x-data="{ sterne: {{ (int) old('bewertung', $feedback->bewertung) }} }">
            @csrf
            <fieldset>
                <legend class="mb-2 font-medium">1. Wie zufrieden warst du insgesamt?</legend>
                <input type="hidden" name="bewertung" :value="sterne || ''">
                <div class="flex gap-1 text-4xl">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" @click="sterne = {{ $i }}" :class="sterne >= {{ $i }} ? 'text-amber-400' : 'text-stone-300'" aria-label="{{ $i }} von 5 Sternen">★</button>
                    @endfor
                </div>
            </fieldset>
            <x-ui.feld name="gut" label="2. Was hat dir gut gefallen?" typ="textarea" :wert="$feedback->gut" />
            <x-ui.feld name="besser" label="3. Was können wir besser machen?" typ="textarea" :wert="$feedback->besser" />
            <x-ui.knopf class="w-full">Absenden</x-ui.knopf>
        </form>
    </div>
</x-layouts.oeffentlich>
