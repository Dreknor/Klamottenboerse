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
            <form id="verkaeufer-filter" method="get" action="{{ route('interessenten.inaktive-verkaeufer') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="richtung" value="{{ $richtung }}">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="monate">Keine Teilnahme seit mindestens (Monate)</label>
                        <input id="monate" name="monate" type="number" min="1" max="600" value="{{ $monate }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="suche">Alle Spalten durchsuchen</label>
                        <input id="suche" name="suche" class="form-control" value="{{ request('suche') }}" placeholder="Suchbegriff, Datum: JJJJ-MM-TT; ohne Teilnahme: nie; ohne Reservierung: keine">
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
            <form id="verkaeufer-loeschen" method="post" action="{{ route('interessenten.inaktive-verkaeufer.destroy', request()->query()) }}">
                @csrf
                @method('DELETE')
                <p>Ausgew&auml;hlte Eintr&auml;ge werden sofort gel&ouml;scht. Die Betroffenen werden per E-Mail informiert und gebeten, sich zu melden, wenn sie weiterhin eingetragen bleiben m&ouml;chten. Ohne g&uuml;ltige E-Mail-Adresse ist keine Massenl&ouml;schung m&ouml;glich.</p>
                <label><input type="checkbox" name="bestaetigung" value="1" required> Ich best&auml;tige die L&ouml;schung der ausgew&auml;hlten Eintr&auml;ge.</label>
                <button type="submit" class="btn btn-danger" id="delete-selected" disabled>Auswahl l&ouml;schen (<span id="selected-count">0</span>)</button>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-page" aria-label="Alle ausw&auml;hlbaren Eintr&auml;ge dieser Seite ausw&auml;hlen"></th>
                            @foreach(['name' => 'Name', 'mail' => 'E-Mail', 'telefon' => 'Telefon / Handy', 'letzte_teilnahme' => 'Letzte Teilnahme', 'created_at' => 'Angelegt am', 'nummern' => 'Reservierte Nummern (aktuelle Börse)'] as $column => $label)
                                <th aria-sort="{{ $sort === $column ? ($richtung === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                    <a href="{{ route('interessenten.inaktive-verkaeufer', array_merge(request()->except('page'), ['sort' => $column, 'richtung' => $sort === $column && $richtung === 'asc' ? 'desc' : 'asc'])) }}">{{ $label }} {{ $sort === $column ? ($richtung === 'asc' ? '↑' : '↓') : '' }}</a>
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <th></th>
                            @foreach(['name', 'mail', 'telefon', 'letzte_teilnahme', 'created_at', 'nummern'] as $column)
                                <th><input class="form-control form-control-sm" form="verkaeufer-filter" name="spalten[{{ $column }}]" value="{{ request('spalten.'.$column) }}" aria-label="Spalte {{ $column }} durchsuchen" placeholder="{{ in_array($column, ['created_at', 'letzte_teilnahme']) ? 'JJJJ-MM-TT' : 'Suchen' }}"></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verkaeufer as $person)
                            <tr>
                                <td><input type="checkbox" class="seller-selection" name="ids[]" value="{{ $person->id }}" form="verkaeufer-loeschen" aria-label="{{ $person->vorname }} {{ $person->nachname }} ausw&auml;hlen" @disabled(!filter_var($person->mail, FILTER_VALIDATE_EMAIL))></td>
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
                            <tr><td colspan="7">Keine Verk&auml;ufer f&uuml;r die gew&auml;hlten Filter gefunden.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $verkaeufer->links() }}</div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/inaktive-verkaeufer.js') }}?v={{ @filemtime(public_path('js/inaktive-verkaeufer.js')) ?: 1 }}"></script>
@endsection
