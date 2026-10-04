@props(['titel', 'wert', 'zusatz' => null, 'href' => null])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <p class="text-sm text-stone-500">{{ $titel }}</p>
    <p class="mt-1 text-2xl font-semibold text-stone-900">
        @if ($href)<a href="{{ $href }}" class="text-stone-900 no-underline hover:underline">{{ $wert }}</a>@else{{ $wert }}@endif
    </p>
    @if ($zusatz)<p class="mt-1 text-xs text-stone-500">{{ $zusatz }}</p>@endif
</div>
