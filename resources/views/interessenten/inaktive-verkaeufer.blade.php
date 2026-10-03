@extends('layouts.app')

@section('content')
    <header class="section-header">
        <h2>Inaktive Verk&auml;ufer</h2>
        <p>Keine Teilnahme seit mindestens {{ $monate }} Monaten (letzte Teilnahme oder Anlage ohne bisherige Teilnahme am oder vor dem {{ $stichtag->format('d.m.Y') }}).</p>
    </header>

    <section class="card">
        <div class="card-body">
            <p>Als Teilnahme z&auml;hlt eine vergebene Verk&auml;ufernummer bei einer bereits stattgefundenen B&ouml;rse, unabh&auml;ngig vom Umsatz. Interessenten ohne bisherige Teilnahme werden ebenfalls angezeigt, wenn sie bereits zu Beginn des gew&auml;hlten Zeitraums angelegt waren.</p>
            <p>&bdquo;Letzte Teilnahme&ldquo; zeigt die letzte Teilnahme aus der gesamten Historie, auch vor dem gew&auml;hlten Zeitraum. Der Zeitraum filtert nur die angezeigten Personen.</p>
            <p>Reservierungen beziehen sich auf die aktuelle B&ouml;rse:
                @if($aktuelleBoerse)
                    {{ $aktuelleBoerse->datum->format('d.m.Y') }}.
                @else
                    Es ist noch keine B&ouml;rse angelegt.
                @endif
            </p>
            <form method="get" action="{{ route('interessenten.inaktive-verkaeufer') }}">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="monate">Keine Teilnahme seit mindestens (Monate)</label>
                        <input id="monate" name="monate" type="number" min="1" max="600" value="{{ $monate }}" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="reservierung">Reservierte Nummern</label>
                        <select id="reservierung" name="reservierung" class="form-control">
                            <option value="alle" @selected($reservierung === 'alle')>Alle</option>
                            <option value="ja" @selected($reservierung === 'ja')>Mit Reservierung</option>
                            <option value="nein" @selected($reservierung === 'nein')>Ohne Reservierung</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Anzeigen</button>
                <a href="{{ route('interessenten.inaktive-verkaeufer') }}" class="btn btn-outline-secondary">Zur&uuml;cksetzen</a>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="card-header">{{ $verkaeufer->total() }} inaktive Verk&auml;ufer</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>E-Mail</th>
                            <th>Telefon / Handy</th>
                            <th>Letzte Teilnahme</th>
                            <th>Angelegt am</th>
                            <th>Reservierte Nummern (aktuelle B&ouml;rse)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verkaeufer as $person)
                            <tr>
                                <td><a href="{{ url('interessent/'.$person->id) }}">{{ $person->nachname }}, {{ $person->vorname }}</a></td>
                                <td>{{ $person->mail }}</td>
                                <td>
                                    {{ $person->telefon }}
                                    @if($person->telefon && $person->handy)<br>@endif
                                    {{ $person->handy }}
                                </td>
                                <td>{{ $person->letzte_teilnahme ? $person->letzte_teilnahme->format('d.m.Y') : 'Noch nie teilgenommen' }}</td>
                                <td>{{ $person->created_at ? $person->created_at->format('d.m.Y') : 'Unbekannt' }}</td>
                                <td>
                                    @forelse($person->reservierteNummern as $nummer)
                                        <span class="label label-info">{{ $nummer->vknummer }}</span>
                                    @empty
                                        Keine
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Keine Verk&auml;ufer f&uuml;r die gew&auml;hlten Filter gefunden.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $verkaeufer->links() }}</div>
    </section>
@endsection
