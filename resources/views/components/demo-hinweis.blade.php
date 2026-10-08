@if (\App\Support\Demo::aktiv())
    <div class="bg-amber-300 px-4 py-2 text-center text-sm text-amber-950" role="note">
        <strong>Demo zum Ausprobieren</strong> – alle Personen sind erfunden, alles wird jede Nacht zurückgesetzt.
        Mails gehen nur an Adressen, die du selbst einträgst (mit „[DEMO]“ im Betreff).
        <a href="{{ route('login') }}" class="font-medium text-amber-950 underline">Rolle wechseln</a>
    </div>
@endif
