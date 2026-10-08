@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Einstellungen: Verkäufer-Reputation</h3>
                <p class="text-muted small mb-0">
                    Teaminternes Instrument – Punkte und Vermerke sind für Verkäufer nicht sichtbar.
                    <a href="{{ route('vermerke.index') }}">Zur Reputations-Übersicht</a>
                </p>
            </div>
            <div class="card-body">
                <h4>Schwellen</h4>
                <form method="post" action="{{ route('einstellungen.reputation.schwellen') }}" class="mb-4" style="max-width: 640px;">
                    @csrf
                    @method('put')
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="warnung_ab">Hinweis ab (Punkte)</label>
                            <input type="number" name="warnung_ab" id="warnung_ab" class="form-control" min="1" required value="{{ old('warnung_ab', $warnungAb) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="sperre_ab">Nur manuelle Vergabe ab (Punkte)</label>
                            <input type="number" name="sperre_ab" id="sperre_ab" class="form-control" min="1" required value="{{ old('sperre_ab', $sperreAb) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="zeitraum_monate">Vermerke zählen (Monate)</label>
                            <input type="number" name="zeitraum_monate" id="zeitraum_monate" class="form-control" min="1" required value="{{ old('zeitraum_monate', $zeitraumMonate) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Schwellen speichern</button>
                </form>

                <h4>Vermerk-Arten und Punkte</h4>
                <p class="text-muted small">
                    Inaktive Arten können nicht mehr neu erfasst werden; bestehende Vermerke bleiben erhalten.
                    Punkte-Änderungen gelten für neue Vermerke – optional auch für bestehende Vermerke mit den bisherigen Standardpunkten.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th style="width: 7rem;">Punkte</th>
                            <th style="width: 7rem;">Reihenfolge</th>
                            <th>aktiv</th>
                            <th>bestehende anpassen</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($typen as $typ)
                            <tr @unless($typ->aktiv) class="text-muted" @endunless>
                                <td><input form="typ-{{ $typ->id }}" type="text" name="label" class="form-control form-control-sm" required value="{{ $typ->label }}" aria-label="Bezeichnung"></td>
                                <td><input form="typ-{{ $typ->id }}" type="number" name="punkte" class="form-control form-control-sm" min="0" max="10" required value="{{ $typ->punkte }}" aria-label="Punkte"></td>
                                <td><input form="typ-{{ $typ->id }}" type="number" name="sortierung" class="form-control form-control-sm" min="0" value="{{ $typ->sortierung }}" aria-label="Reihenfolge"></td>
                                <td><input form="typ-{{ $typ->id }}" type="checkbox" name="aktiv" value="1" @checked($typ->aktiv) aria-label="aktiv"></td>
                                <td><input form="typ-{{ $typ->id }}" type="checkbox" name="bestehende_anpassen" value="1" aria-label="bestehende Vermerke anpassen"></td>
                                <td>
                                    <form id="typ-{{ $typ->id }}" method="post" action="{{ route('einstellungen.reputation.typ.update', $typ->id) }}">
                                        @csrf
                                        @method('put')
                                        <button type="submit" class="btn btn-sm btn-secondary">speichern</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><input form="typ-neu" type="text" name="label" class="form-control form-control-sm" required placeholder="Neue Vermerk-Art" aria-label="Bezeichnung neue Vermerk-Art"></td>
                            <td><input form="typ-neu" type="number" name="punkte" class="form-control form-control-sm" min="0" max="10" required value="1" aria-label="Punkte"></td>
                            <td><input form="typ-neu" type="number" name="sortierung" class="form-control form-control-sm" min="0" value="{{ ($typen->max('sortierung') ?? 0) + 10 }}" aria-label="Reihenfolge"></td>
                            <td colspan="2"></td>
                            <td>
                                <form id="typ-neu" method="post" action="{{ route('einstellungen.reputation.typ.store') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">anlegen</button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
