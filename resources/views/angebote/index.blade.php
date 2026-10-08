@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Angebotsübersicht</h3>
                <p class="text-muted small mb-0">
                    @if ($klamottenboerse && $klamottenboerse->datum)
                        Klamottenbörse am {{ $klamottenboerse->datum->format('d.m.Y') }} –
                    @endif
                    Angaben der Verkäufer (Registrierung / Verkäufer-Portal) und bereits im Portal erfasste Artikel.
                    Artikel ohne gewählte Kategorie werden anhand ihrer Größe zugeordnet.
                    <a href="{{ route('einstellungen.kategorien') }}">Kategorien bearbeiten</a>
                </p>
            </div>
            <div class="card-body">
                <h4>Nach Kategorie</h4>
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Kategorie</th>
                        <th class="text-right">Verkäufer</th>
                        <th class="text-right">erfasste Artikel</th>
                        <th>VK-Nummern</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($kategorien as $kategorie)
                        @continue(!$kategorie['aktiv'] && $kategorie['verkaeufer']->isEmpty() && $kategorie['artikel'] === 0)
                        <tr>
                            <td>{{ $kategorie['label'] }}</td>
                            <td class="text-right">{{ $kategorie['verkaeufer']->count() }}</td>
                            <td class="text-right">{{ $kategorie['artikel'] }}</td>
                            <td class="small">{{ $kategorie['verkaeufer']->pluck('vknummer')->implode(', ') }}</td>
                        </tr>
                    @endforeach
                    <tr class="text-muted">
                        <td>keine Angabe / nicht zuordenbar</td>
                        <td class="text-right">{{ $ohneKategorie['verkaeufer']->count() }}</td>
                        <td class="text-right">{{ $ohneKategorie['artikel'] }}</td>
                        <td class="small">{{ $ohneKategorie['verkaeufer']->pluck('vknummer')->implode(', ') }}</td>
                    </tr>
                    </tbody>
                </table>

                <h4 class="mt-4">Nach Verkäufer</h4>
                <table class="table table-sm table-striped">
                    <thead>
                    <tr>
                        <th>VK-Nr.</th>
                        <th>Verkäufer</th>
                        <th>bringt überwiegend</th>
                        <th class="text-right">erfasste Artikel</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($zeilen as $zeile)
                        <tr>
                            <td>{{ $zeile['vknummer']->vknummer }}</td>
                            <td>
                                <a href="{{ url('interessent/'.$zeile['interessent']->id) }}">{{ $zeile['interessent']->nachname }}, {{ $zeile['interessent']->vorname }}</a>
                                @include('vermerke._badge', ['interessent' => $zeile['interessent']])
                            </td>
                            <td class="small">{{ implode(', ', $zeile['kategorien']) ?: '–' }}</td>
                            <td class="text-right">{{ $zeile['artikel_anzahl'] ?: '–' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Für die aktuelle Börse sind noch keine Nummern vergeben.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
