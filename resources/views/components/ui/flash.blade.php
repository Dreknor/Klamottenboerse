@foreach (['erfolg' => 'border-emerald-300 bg-emerald-50 text-emerald-900', 'hinweis' => 'border-amber-300 bg-amber-50 text-amber-900', 'fehler' => 'border-red-300 bg-red-50 text-red-900'] as $art => $klassen)
    @if (session($art))
        <div class="mb-4 rounded-lg border px-4 py-3 {{ $klassen }}" role="status">{{ session($art) }}</div>
    @endif
@endforeach
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-900" role="alert">
        <p class="font-medium">Bitte prüfe die markierten Felder:</p>
        <ul class="mt-1 list-disc pl-5 text-sm">
            @foreach ($errors->all() as $fehler)
                <li>{{ $fehler }}</li>
            @endforeach
        </ul>
    </div>
@endif
