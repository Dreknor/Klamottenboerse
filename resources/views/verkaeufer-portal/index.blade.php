@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 800px;">
    <div class="app-card p-4 mt-4">
        <h3 class="mb-1">Verkäufer-Portal</h3>
        <p class="text-muted">VK-Nummer {{ $vknummer->vknummer }} &mdash; {{ $interessent->vorname }} {{ $interessent->nachname }}</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h5 class="mt-4">Neuen Artikel erfassen</h5>
        <form method="post" action="{{ route('verkaeuferPortal.store', ['uuid' => $uuid]) }}" class="row g-2">
            @csrf
            <div class="form-group col-md-4">
                <label for="beschreibung">Beschreibung</label>
                <input type="text" name="beschreibung" id="beschreibung" class="form-control" required value="{{ old('beschreibung') }}">
            </div>
            <div class="form-group col-md-3">
                <label for="kategorie">Kategorie</label>
                <input type="text" name="kategorie" id="kategorie" class="form-control" value="{{ old('kategorie') }}">
            </div>
            <div class="form-group col-md-2">
                <label for="groesse">Größe</label>
                <input type="text" name="groesse" id="groesse" class="form-control" value="{{ old('groesse') }}">
            </div>
            <div class="form-group col-md-2">
                <label for="preis">Preis (€)</label>
                <input type="number" step="0.5" min="0.5" name="preis" id="preis" class="form-control" required value="{{ old('preis') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="app-button">+</button>
            </div>
        </form>

        <h5 class="mt-4">Meine Artikel ({{ $artikel->count() }})</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Beschreibung</th>
                    <th>Kategorie</th>
                    <th>Größe</th>
                    <th>Preis</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($artikel as $a)
                    <tr>
                        <td>{{ $vknummer->vknummer }}-{{ $a->artikelnummer }}</td>
                        <td>{{ $a->beschreibung }}</td>
                        <td>{{ $a->kategorie }}</td>
                        <td>{{ $a->groesse }}</td>
                        <td>{{ number_format($a->preis, 2) }} €</td>
                        <td>
                            <form method="post" action="{{ route('verkaeuferPortal.destroy', ['uuid' => $uuid, 'artikel' => $a->id]) }}" onsubmit="return confirm('Artikel wirklich entfernen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Entfernen</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Noch keine Artikel erfasst.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($artikel->count() > 0)
            <a href="{{ route('verkaeuferPortal.etiketten', ['uuid' => $uuid]) }}" class="app-button" target="_blank">Etiketten drucken</a>
        @endif

        <h5 class="mt-4">Live-Verkaufsansicht</h5>
        @if (!$liveVerkaeufeFreigegeben)
            <p class="text-muted">Diese Ansicht wird während der Veranstaltung vom Veranstalter freigeschaltet.</p>
        @else
            <div class="alert alert-info">Aktueller Erlös: <strong>{{ number_format($aktuellerErloes, 2, ',', '.') }} €</strong> ({{ $verkaufteArtikel->count() }} verkaufte Artikel)</div>
            <table class="table table-sm">
                <thead>
                    <tr><th>Artikel-Nr.</th><th>Preis</th></tr>
                </thead>
                <tbody>
                    @forelse ($verkaufteArtikel->sortBy('artikelnummer') as $verkauft)
                        <tr><td>{{ $verkauft->artikelnummer }}</td><td>{{ number_format($verkauft->betrag, 2, ',', '.') }} €</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-muted">Noch keine Verkäufe erfasst.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <script>
                // Einfaches Live-Update: Seite alle 30 Sekunden neu laden, solange diese Sektion sichtbar ist.
                setTimeout(() => window.location.reload(), 30000);
            </script>
        @endif
    </div>
</div>
@endsection
