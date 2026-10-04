<x-layouts.admin titel="Protokolle">
    <x-ui.kopf titel="Protokolle" unter="Besprechungen des Teams – durchsuchbar über alle Börsen.">
        <x-ui.knopf :href="route('admin.protokolle.create')">Neues Protokoll</x-ui.knopf>
    </x-ui.kopf>

    <x-ui.karte>
        <form method="get" class="mb-4 flex flex-wrap gap-2">
            <input name="suche" value="{{ $suche }}" class="feld max-w-sm" placeholder="Suchen in Titel, Text, Teilnehmenden">
            <select name="boerse" class="feld max-w-xs" onchange="this.form.submit()">
                <option value="">Alle Börsen</option>
                @foreach ($boersen as $id => $titel)<option value="{{ $id }}" @selected(request('boerse') == $id)>{{ $titel }}</option>@endforeach
            </select>
            <x-ui.knopf art="sekundaer">Suchen</x-ui.knopf>
        </form>

        @forelse ($protokolle as $p)
            <a href="{{ route('admin.protokolle.show', $p) }}" class="block border-b border-stone-100 py-3 text-stone-800 no-underline last:border-0 hover:bg-stone-50">
                <span class="font-medium">{{ $p->titel }}</span>
                <span class="block text-sm text-stone-500">{{ $p->datum->format('d.m.Y') }}{{ $p->boerse ? ' · '.$p->boerse->titel : '' }}{{ $p->teilnehmende ? ' · '.$p->teilnehmende : '' }}</span>
                @if ($suche !== '')
                    <span class="block text-xs text-stone-600">{{ Str::excerpt((string) $p->inhalt, $suche, ['radius' => 80]) }}</span>
                @endif
            </a>
        @empty
            <p class="text-stone-500">Keine Protokolle gefunden.</p>
        @endforelse
        <div class="mt-4">{{ $protokolle->links() }}</div>
    </x-ui.karte>
</x-layouts.admin>
