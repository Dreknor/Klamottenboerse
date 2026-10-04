<x-layouts.admin :titel="$person->name">
    <x-ui.kopf :titel="$person->name" :unter="collect([$person->email, $person->telefon, $person->kinderhaus_bezug->hatVorlauf() ? $person->kinderhaus_bezug->label() : null])->filter()->implode(' · ')">
        @if ($person->email)
            <form method="post" action="{{ route('admin.personen.login-link', $person) }}">@csrf<x-ui.knopf art="sekundaer">Portal-Link senden</x-ui.knopf></form>
        @endif
        <x-ui.knopf art="sekundaer" :href="route('admin.personen.edit', $person)">Bearbeiten</x-ui.knopf>
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.karte titel="Teilnahmen als Verkäufer">
            <table class="tabelle">
                <thead><tr><th>Börse</th><th>Nr.</th><th>Status</th><th>Auszahlung</th></tr></thead>
                <tbody>
                @forelse ($person->teilnahmen->sortByDesc(fn ($t) => $t->boerse->verkaufstag) as $t)
                    <tr>
                        <td>{{ $t->boerse->titel }}</td>
                        <td class="font-mono">{{ $t->nummer ?? '–' }}</td>
                        <td><x-ui.abzeichen :farbe="$t->status->farbe()">{{ $t->status->label() }}</x-ui.abzeichen></td>
                        <td>{{ $t->abrechnung ? \App\Support\Geld::format($t->abrechnung->auszahlung_cent) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-stone-500">Noch nie verkauft.</td></tr>
                @endforelse
                </tbody>
            </table>
            @if ($person->reservierungen->isNotEmpty())
                <p class="mt-3 text-sm">Reservierte Nummer(n): <strong>{{ $person->reservierungen->pluck('nummer')->implode(', ') }}</strong></p>
            @endif
        </x-ui.karte>

        <x-ui.karte titel="Schichten als Helfer">
            @forelse ($person->einteilungen->sortByDesc(fn ($e) => $e->schicht->beginn) as $e)
                <p class="border-b border-stone-100 py-1.5 last:border-0">
                    {{ $e->schicht->boerse->titel }}: {{ $e->schicht->bereich }}, {{ $e->schicht->beginn->format('d.m. H:i') }}–{{ $e->schicht->ende->format('H:i') }}
                    @if ($e->status === \App\Enums\EinteilungStatus::Abgesagt) <x-ui.abzeichen>abgesagt</x-ui.abzeichen> @endif
                </p>
            @empty
                <p class="text-stone-500">Noch keine Schichten.</p>
            @endforelse
        </x-ui.karte>

        <x-ui.karte titel="Notizen">
            <form method="post" action="{{ route('admin.personen.notiz', $person) }}" class="mb-4 space-y-2">
                @csrf
                <textarea name="text" rows="2" class="feld" placeholder="Neue Notiz …" required></textarea>
                <x-ui.knopf groesse="klein">Notiz speichern</x-ui.knopf>
            </form>
            @foreach ($person->notizen as $notiz)
                <div class="border-b border-stone-100 py-2 last:border-0">
                    <p class="whitespace-pre-line">{{ $notiz->text }}</p>
                    <p class="text-xs text-stone-500">{{ $notiz->autor?->name }}, {{ $notiz->created_at->format('d.m.Y H:i') }}</p>
                </div>
            @endforeach
        </x-ui.karte>

        <x-ui.karte titel="Mails">
            <h3 class="mb-1 text-sm font-medium text-stone-500">Eingegangen</h3>
            @forelse ($posteingang as $mail)
                <p class="py-1"><a href="{{ route('admin.posteingang.show', $mail) }}">{{ $mail->betreff ?: '(ohne Betreff)' }}</a> <span class="text-xs text-stone-500">{{ $mail->empfangen_at->format('d.m.Y') }}</span></p>
            @empty
                <p class="text-sm text-stone-500">Keine.</p>
            @endforelse
            <h3 class="mb-1 mt-4 text-sm font-medium text-stone-500">Gesendet</h3>
            @forelse ($nachrichten as $n)
                <p class="py-1">{{ $n->betreff }} <span class="text-xs text-stone-500">{{ ($n->versendet_at ?? $n->created_at)->format('d.m.Y') }} · {{ $n->status->value }}</span></p>
            @empty
                <p class="text-sm text-stone-500">Keine.</p>
            @endforelse
        </x-ui.karte>
    </div>

    @if ($aktivitaeten->isNotEmpty())
        <x-ui.karte titel="Verlauf" class="mt-6">
            @foreach ($aktivitaeten as $a)
                <p class="py-1 text-sm">{{ $a->created_at->format('d.m.Y H:i') }} – {{ $a->description }} {{ $a->properties['nummer'] ?? '' }}</p>
            @endforeach
        </x-ui.karte>
    @endif
</x-layouts.admin>
