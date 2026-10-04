<section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
    @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
    <div class="inhalt {{ $b['titel'] ? 'mt-3' : '' }}">{!! $k->text($b['text']) !!}</div>
</section>
