@props(['name', 'label', 'typ' => 'text', 'wert' => null, 'hilfe' => null])
<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</label>
    @if ($typ === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" {{ $attributes->except('class')->merge(['class' => 'feld']) }}>{{ old($name, $wert) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $typ }}" value="{{ old($name, $wert) }}" {{ $attributes->except('class')->merge(['class' => 'feld']) }}>
    @endif
    @if ($hilfe)<p class="mt-1 text-xs text-stone-500">{{ $hilfe }}</p>@endif
    @error($name)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
