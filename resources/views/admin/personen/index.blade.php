<x-layouts.admin titel="Personen">
    <x-ui.kopf titel="Personen" unter="Verkäufer, Helfer und Team – eine Kartei für alle.">
        <x-ui.knopf :href="route('admin.personen.create')">Neue Person</x-ui.knopf>
    </x-ui.kopf>

    <x-ui.karte>
        <form method="get" class="mb-4 flex flex-wrap gap-2">
            <input name="suche" value="{{ request('suche') }}" class="feld max-w-sm" placeholder="Name, E-Mail oder Telefon" autofocus>
            <select name="rolle" class="feld max-w-xs" onchange="this.form.submit()">
                <option value="">Alle</option>
                @foreach (\Database\Seeders\GrunddatenSeeder::ROLLEN as $rolle => $text)
                    <option value="{{ $rolle }}" @selected(request('rolle') === $rolle)>{{ $text }}</option>
                @endforeach
            </select>
            <x-ui.knopf art="sekundaer">Suchen</x-ui.knopf>
        </form>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Name</th><th>E-Mail</th><th>Telefon</th><th>Kinderhaus</th><th>Börsen</th><th>Rollen</th></tr></thead>
                <tbody>
                @forelse ($personen as $p)
                    <tr>
                        <td><a href="{{ route('admin.personen.show', $p) }}">{{ $p->nachname }}, {{ $p->vorname }}</a></td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->telefon }}</td>
                        <td>{{ $p->kinderhaus_bezug->hatVorlauf() ? $p->kinderhaus_bezug->label() : '' }}</td>
                        <td>{{ $p->teilnahmen_count }}</td>
                        <td>@foreach ($p->roles as $r)<x-ui.abzeichen class="mr-1">{{ $r->name }}</x-ui.abzeichen>@endforeach</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-stone-500">Niemand gefunden.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $personen->links() }}</div>
    </x-ui.karte>
</x-layouts.admin>
