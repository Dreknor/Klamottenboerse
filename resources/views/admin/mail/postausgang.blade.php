<x-layouts.admin titel="Postausgang">
    <x-ui.kopf titel="Postausgang" :unter="$wartend.' Mail(s) warten · in dieser Stunde dürfen noch '.$kontingent.' raus (Limit in den Einstellungen)'" />

    <x-ui.karte>
        <form method="get" class="mb-4 flex flex-wrap gap-2">
            <input name="suche" value="{{ request('suche') }}" class="feld max-w-xs" placeholder="E-Mail oder Betreff">
            <select name="status" class="feld max-w-xs" onchange="this.form.submit()">
                <option value="">Alle</option>
                @foreach (\App\Enums\NachrichtStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst($s->value) }}</option>
                @endforeach
            </select>
            <x-ui.knopf art="sekundaer">Filtern</x-ui.knopf>
        </form>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Erstellt</th><th>An</th><th>Betreff</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($nachrichten as $n)
                    <tr>
                        <td class="whitespace-nowrap">{{ $n->created_at->format('d.m. H:i') }}</td>
                        <td>@if ($n->person)<a href="{{ route('admin.personen.show', $n->person) }}">{{ $n->email }}</a>@else{{ $n->email }}@endif</td>
                        <td>{{ $n->betreff }}</td>
                        <td>
                            <x-ui.abzeichen :farbe="['wartend' => 'amber', 'versendet' => 'emerald', 'fehler' => 'red'][$n->status->value]">{{ $n->status->value }}</x-ui.abzeichen>
                            @if ($n->fehler)<span class="block max-w-xs truncate text-xs text-red-700" title="{{ $n->fehler }}">{{ $n->fehler }}</span>@endif
                        </td>
                        <td class="text-right">
                            @if ($n->status === \App\Enums\NachrichtStatus::Fehler)
                                <form method="post" action="{{ route('admin.postausgang.erneut', $n) }}">@csrf<button class="text-sm text-marke-700 hover:underline">Erneut senden</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-stone-500">Keine Mails.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $nachrichten->links() }}</div>
    </x-ui.karte>
</x-layouts.admin>
