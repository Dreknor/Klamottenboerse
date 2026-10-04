<x-layouts.oeffentlich :titel="$titel">
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        <h1>{{ $titel }}</h1>
        <p class="mt-3 text-stone-600">{{ $text }}</p>
        <x-ui.knopf art="sekundaer" :href="route('start')" class="mt-6">Zur Startseite</x-ui.knopf>
    </div>
</x-layouts.oeffentlich>
