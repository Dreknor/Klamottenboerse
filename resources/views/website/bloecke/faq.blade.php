<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
    @if ($b['titel'])<h2>{{ $b['titel'] }}</h2>@endif
    <div class="mt-3 divide-y divide-stone-100">
        @foreach ($b['eintraege'] as $e)
            <details class="group py-3">
                <summary class="cursor-pointer list-none font-medium text-stone-900">
                    <span class="mr-2 inline-block text-marke-600 transition group-open:rotate-90" aria-hidden="true">›</span>{{ $k->zeile($e['frage']) }}
                </summary>
                <div class="inhalt mt-2 pl-5">{!! $k->text($e['antwort']) !!}</div>
            </details>
        @endforeach
    </div>
</section>
