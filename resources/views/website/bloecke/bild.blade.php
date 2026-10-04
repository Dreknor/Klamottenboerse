@php
    $m = $k->bild($b['media_id']);
    $src = $m ? $m->getUrl('web') : ($b['pfad'] ? asset($b['pfad']) : null);
@endphp
@if ($src)
    <figure class="{{ $b['breite'] === 'halb' ? 'mx-auto max-w-xl' : '' }}">
        <img src="{{ $src }}" alt="{{ $b['alt'] ?: $m?->getCustomProperty('alt') }}" class="w-full rounded-2xl object-cover shadow-sm ring-1 ring-stone-200" loading="lazy">
        @if ($b['beschriftung'])<figcaption class="mt-2 text-center text-sm text-stone-600">{{ $b['beschriftung'] }}</figcaption>@endif
    </figure>
@endif
