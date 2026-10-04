@props(['titel' => null, 'aktionen' => null])
<section {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-5 shadow-sm']) }}>
    @if ($titel || $aktionen)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            @if ($titel)<h2>{{ $titel }}</h2>@endif
            @if ($aktionen)<div class="flex flex-wrap gap-2">{{ $aktionen }}</div>@endif
        </div>
    @endif
    {{ $slot }}
</section>
