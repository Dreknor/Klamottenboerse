<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
    @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
    <ul class="mt-3 list-disc space-y-1 pl-5 text-stone-700">
        @foreach ($b['eintraege'] as $eintrag)
            <li>{{ $k->zeile($eintrag) }}</li>
        @endforeach
    </ul>
</section>
