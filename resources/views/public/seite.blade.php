<x-layouts.oeffentlich :titel="$seite->titel">
    <article class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 md:p-10">
        <h1 class="text-3xl">{{ $seite->titel }}</h1>
        <div class="inhalt mt-6">{!! $seite->html() !!}</div>
        <p class="mt-8 text-sm text-stone-500">Stand: {{ $seite->updated_at->isoFormat('D. MMMM YYYY') }}</p>
    </article>
</x-layouts.oeffentlich>
