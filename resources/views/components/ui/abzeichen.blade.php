@props(['farbe' => 'stone'])
@php
    $farben = [
        'stone' => 'bg-stone-100 text-stone-700', 'amber' => 'bg-amber-100 text-amber-800',
        'sky' => 'bg-sky-100 text-sky-800', 'indigo' => 'bg-indigo-100 text-indigo-800',
        'emerald' => 'bg-emerald-100 text-emerald-800', 'red' => 'bg-red-100 text-red-800',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '.($farben[$farbe] ?? $farben['stone'])]) }}>{{ $slot }}</span>
