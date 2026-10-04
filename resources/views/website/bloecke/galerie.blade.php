@php $bilder = collect($b['bilder'])->map(fn ($id) => $k->bild($id))->filter()->values(); @endphp
@if ($bilder->isNotEmpty())
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
        @if ($b['titel'])<h2>{{ $b['titel'] }}</h2>@endif
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($bilder as $m)
                <a href="{{ $m->getUrl('web') }}" target="_blank" class="{{ $loop->first ? 'col-span-2 row-span-2' : '' }}">
                    <img src="{{ $loop->first ? $m->getUrl('web') : $m->getUrl('klein') }}" alt="{{ $m->getCustomProperty('alt') }}"
                         class="h-full w-full rounded-xl object-cover {{ $loop->first ? '' : 'aspect-[3/2]' }}" loading="lazy">
                </a>
            @endforeach
        </div>
    </section>
@endif
