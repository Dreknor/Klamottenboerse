@props(['titel', 'unter' => null])
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1>{{ $titel }}</h1>
        @if ($unter)<p class="mt-1 text-stone-500">{{ $unter }}</p>@endif
    </div>
    @if (trim($slot))<div class="flex flex-wrap gap-2">{{ $slot }}</div>@endif
</div>
