<x-layouts.admin titel="Posteingang">
    <x-ui.kopf titel="Posteingang" unter="Mails an das Postfach der Börse. Neue Mails werden alle 5 Minuten abgerufen.">
        @if ($konfiguriert)
            <form method="post" action="{{ route('admin.posteingang.abrufen') }}">@csrf<x-ui.knopf art="sekundaer">Jetzt abrufen</x-ui.knopf></form>
        @endif
    </x-ui.kopf>

    @unless ($konfiguriert)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
            Das Postfach ist noch nicht eingerichtet. Ein Admin muss die Zugangsdaten (IMAP_HOST, IMAP_USERNAME, IMAP_PASSWORD) in der Server-Konfiguration eintragen.
        </div>
    @endunless

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach (['offen' => 'Offen', 'erledigt' => 'Erledigt', 'spam' => 'Spam', 'alle' => 'Alle'] as $wert => $text)
            <a href="{{ route('admin.posteingang.index', ['ansicht' => $wert]) }}" class="rounded-full px-3 py-1 no-underline {{ $ansicht === $wert ? 'bg-marke-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-300' }}">{{ $text }}</a>
        @endforeach
        <form method="get" class="ml-auto"><input type="hidden" name="ansicht" value="{{ $ansicht }}"><input name="suche" value="{{ request('suche') }}" class="feld py-1" placeholder="Suchen …"></form>
        @if ($ansicht === 'offen' && $mails->total() > 0)
            <form method="post" action="{{ route('admin.posteingang.alle-erledigt') }}"
                  onsubmit="return confirm('{{ request('suche') ? 'Alle '.$mails->total().' gefundenen Mails' : 'Alle '.$mails->total().' offenen Mails' }} als erledigt markieren? Sie bleiben unter „Erledigt“ erhalten.')">
                @csrf
                <input type="hidden" name="suche" value="{{ request('suche') }}">
                <x-ui.knopf art="sekundaer" groesse="klein">{{ request('suche') ? 'Treffer' : 'Alle' }} als erledigt markieren ({{ $mails->total() }})</x-ui.knopf>
            </form>
        @endif
    </div>

    <x-ui.karte class="p-0">
        @forelse ($mails as $mail)
            <a href="{{ route('admin.posteingang.show', $mail) }}" class="flex items-start gap-3 border-b border-stone-100 px-5 py-3 text-stone-800 no-underline last:border-0 hover:bg-stone-50">
                <span class="mt-2 h-2 w-2 shrink-0 rounded-full {{ $mail->gelesen_at ? 'bg-transparent' : 'bg-marke-600' }}"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex justify-between gap-2">
                        <span class="truncate {{ $mail->gelesen_at ? '' : 'font-semibold' }}">{{ $mail->person?->name ?? ($mail->von_name ?: $mail->von_email) }}</span>
                        <span class="shrink-0 text-xs text-stone-500">{{ $mail->empfangen_at->format('d.m. H:i') }}</span>
                    </div>
                    <p class="truncate text-sm">{{ $mail->betreff ?: '(ohne Betreff)' }}</p>
                    <p class="truncate text-xs text-stone-500">{{ Str::limit($mail->text, 120) }}</p>
                </div>
                @if ($mail->beantwortet_at)<x-ui.abzeichen farbe="emerald">beantwortet</x-ui.abzeichen>@endif
                @unless ($mail->person_id)<x-ui.abzeichen farbe="amber">unbekannt</x-ui.abzeichen>@endunless
            </a>
        @empty
            <p class="px-5 py-4 text-stone-500">Keine Mails.</p>
        @endforelse
    </x-ui.karte>
    <div class="mt-4">{{ $mails->links() }}</div>
</x-layouts.admin>
