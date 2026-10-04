<section class="rounded-2xl p-6 ring-1 {{ $b['farbe'] === 'grau' ? 'bg-stone-100 ring-stone-200' : 'bg-marke-50 ring-marke-100' }}">
    @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
    <div class="inhalt {{ $b['titel'] ? 'mt-2' : '' }}">{!! $k->text($b['text']) !!}</div>
</section>
