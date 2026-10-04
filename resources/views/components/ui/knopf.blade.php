@props(['art' => 'primaer', 'href' => null, 'groesse' => 'normal'])
@php
    $basis = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium no-underline transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50';
    $groessen = ['klein' => 'px-2.5 py-1 text-sm', 'normal' => 'px-4 py-2', 'gross' => 'px-6 py-4 text-lg'];
    $arten = [
        'primaer' => 'bg-marke-600 text-white hover:bg-marke-700 focus:ring-marke-500',
        'sekundaer' => 'border border-stone-300 bg-white text-stone-700 hover:bg-stone-100 focus:ring-stone-400',
        'gefahr' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'erfolg' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'leise' => 'text-stone-600 hover:bg-stone-100 focus:ring-stone-300',
    ];
    $klassen = $basis.' '.$groessen[$groesse].' '.$arten[$art];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $klassen]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $klassen]) }}>{{ $slot }}</button>
@endif
