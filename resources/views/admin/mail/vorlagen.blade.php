<x-layouts.admin titel="Mailvorlagen">
    <x-ui.kopf titel="Mailvorlagen" unter="Texte aller Mails. Platzhalter wie {vorname} werden beim Versand automatisch ersetzt." />
    <x-ui.karte>
        <table class="tabelle">
            <thead><tr><th>Name</th><th>Betreff</th><th>Zuletzt geändert</th></tr></thead>
            <tbody>
            @foreach ($vorlagen as $v)
                <tr>
                    <td><a href="{{ route('admin.mailvorlagen.edit', $v) }}">{{ $v->name }}</a></td>
                    <td>{{ $v->betreff }}</td>
                    <td class="text-stone-500">{{ $v->updated_at->format('d.m.Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-ui.karte>
</x-layouts.admin>
