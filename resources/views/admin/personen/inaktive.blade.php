<x-layouts.admin titel="Datenschutz: Inaktive">
    <x-ui.kopf titel="Datenschutz: inaktive Personen" :unter="'Wer '.$monate.' Monate an keiner Börse teilgenommen und nicht geholfen hat, wird automatisch angeschrieben und nach '.$frist.' Tagen ohne Rückmeldung gelöscht. Team-Mitglieder sind ausgenommen.'" />

    <div class="grid gap-6 lg:grid-cols-3">
        @foreach ([
            ['Werden beim nächsten Lauf angeschrieben', $kandidaten, null],
            ['Angeschrieben – Löschung nach Frist', $angeschrieben, 'inaktiv_angeschrieben_at'],
            ['Löschung selbst beantragt (nach der Börse)', $beantragt, 'loeschung_angefragt_at'],
        ] as [$titel, $liste, $datum])
            <x-ui.karte :titel="$titel.' ('.$liste->count().')'">
                @forelse ($liste as $p)
                    <p class="border-b border-stone-100 py-1.5 text-sm last:border-0">
                        <a href="{{ route('admin.personen.show', $p) }}">{{ $p->nachname }}, {{ $p->vorname }}</a>
                        @if ($datum)
                            <span class="text-stone-500">· {{ $p->{$datum}->format('d.m.Y') }}@if ($datum === 'inaktiv_angeschrieben_at') → löschen ab {{ $p->{$datum}->copy()->addDays($frist)->format('d.m.Y') }}@endif</span>
                        @endif
                        @unless ($p->email)<x-ui.abzeichen>ohne E-Mail</x-ui.abzeichen>@endunless
                    </p>
                @empty
                    <p class="text-sm text-stone-500">Niemand.</p>
                @endforelse
            </x-ui.karte>
        @endforeach
    </div>
    <p class="mt-4 text-sm text-stone-500">Wer auf den Link in der Mail klickt, bleibt erhalten. Einzelne Personen lassen sich in der Personenansicht sofort löschen.</p>
</x-layouts.admin>
