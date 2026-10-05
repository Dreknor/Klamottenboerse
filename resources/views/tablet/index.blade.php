<x-layouts.tablet :titel="$boerse->titel" :zurueck="auth()->user()->istOrga() ? route('admin.dashboard') : route('admin.aufgaben.index')"
                   :zurueck-text="auth()->user()->istOrga() ? 'Zurück' : 'Team-Bereich'">
    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('tablet.annahme') }}" class="rounded-2xl bg-white p-8 text-center text-stone-900 no-underline shadow-sm hover:ring-2 hover:ring-marke-500">
            <p class="text-5xl">📦</p><p class="mt-3 text-2xl font-semibold">Annahme</p><p class="text-stone-500">Kisten entgegennehmen</p>
        </a>
        <a href="{{ route('tablet.rueckpacken') }}" class="rounded-2xl bg-white p-8 text-center text-stone-900 no-underline shadow-sm hover:ring-2 hover:ring-marke-500">
            <p class="text-5xl">🔁</p><p class="mt-3 text-2xl font-semibold">Rückpacken</p><p class="text-stone-500">Was ist verkauft, was kommt zurück?</p>
        </a>
        <a href="{{ route('tablet.ausgabe') }}" class="rounded-2xl bg-white p-8 text-center text-stone-900 no-underline shadow-sm hover:ring-2 hover:ring-marke-500">
            <p class="text-5xl">💶</p><p class="mt-3 text-2xl font-semibold">Ausgabe</p><p class="text-stone-500">Kiste und Erlös ausgeben</p>
        </a>
    </div>
</x-layouts.tablet>
