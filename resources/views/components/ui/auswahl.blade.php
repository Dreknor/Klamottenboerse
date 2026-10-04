@props(['name', 'label', 'optionen' => [], 'wert' => null, 'leer' => null])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'feld']) }}>
        @if ($leer !== null)<option value="">{{ $leer }}</option>@endif
        @foreach ($optionen as $schluessel => $text)
            <option value="{{ $schluessel }}" @selected((string) old($name, $wert) === (string) $schluessel)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
