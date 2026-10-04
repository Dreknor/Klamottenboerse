<x-layouts.admin titel="Börsen">
    <x-ui.kopf titel="Börsen verwalten" unter="Neue Börsen am einfachsten als Kopie der letzten anlegen.">
        <x-ui.knopf :href="route('admin.boersen.create')">Neue Börse</x-ui.knopf>
    </x-ui.kopf>

    <x-ui.karte>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Börse</th><th>Verkaufstag</th><th>Ort</th><th>Verkäufer</th><th>Phase</th><th></th></tr></thead>
                <tbody>
                @forelse ($boersen as $boerse)
                    <tr>
                        <td class="font-medium">{{ $boerse->titel }}</td>
                        <td>{{ $boerse->verkaufstag->format('d.m.Y') }}</td>
                        <td>{{ $boerse->ort?->name }}</td>
                        <td>{{ $boerse->verkaeufer_count }} / {{ $boerse->kapazitaet }}</td>
                        <td><x-ui.abzeichen>{{ $boerse->phase()->label() }}</x-ui.abzeichen></td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('admin.boersen.edit', $boerse) }}">Bearbeiten</a>
                            <form method="post" action="{{ route('admin.boersen.abschliessen', $boerse) }}" class="ml-3 inline">
                                @csrf
                                <button class="text-stone-600 hover:underline">{{ $boerse->status === \App\Enums\BoerseStatus::Abgeschlossen ? 'Wieder öffnen' : 'Abschließen' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-stone-500">Noch keine Börse angelegt.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.karte>
</x-layouts.admin>
