@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Einstellungen: Angebotskategorien</h3>
                <p class="text-muted small mb-0">
                    Diese Kategorien wählen Verkäufer bei der Registrierung und im Verkäufer-Portal aus.
                    Mit einem Größenbereich werden im Portal erfasste Artikel ohne Kategorie automatisch zugeordnet.
                    Inaktive Kategorien sind nicht mehr auswählbar, bestehende Angaben bleiben erhalten.
                    <a href="{{ route('angebote.index') }}">Zur Angebotsübersicht</a>
                </p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Bezeichnung</th>
                            <th>Gruppe</th>
                            <th style="width: 6rem;">Größe von</th>
                            <th style="width: 6rem;">Größe bis</th>
                            <th style="width: 6rem;">Reihenfolge</th>
                            <th>aktiv</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($kategorien as $kategorie)
                            <tr @unless($kategorie->aktiv) class="text-muted" @endunless>
                                <td><input form="kat-{{ $kategorie->id }}" type="text" name="label" class="form-control form-control-sm" required value="{{ $kategorie->label }}" aria-label="Bezeichnung"></td>
                                <td><input form="kat-{{ $kategorie->id }}" type="text" name="gruppe" class="form-control form-control-sm" required value="{{ $kategorie->gruppe }}" list="gruppen" aria-label="Gruppe"></td>
                                <td><input form="kat-{{ $kategorie->id }}" type="number" name="groesse_von" class="form-control form-control-sm" min="0" max="999" value="{{ $kategorie->groesse_von }}" aria-label="Größe von"></td>
                                <td><input form="kat-{{ $kategorie->id }}" type="number" name="groesse_bis" class="form-control form-control-sm" min="0" max="999" value="{{ $kategorie->groesse_bis }}" aria-label="Größe bis"></td>
                                <td><input form="kat-{{ $kategorie->id }}" type="number" name="sortierung" class="form-control form-control-sm" min="0" value="{{ $kategorie->sortierung }}" aria-label="Reihenfolge"></td>
                                <td><input form="kat-{{ $kategorie->id }}" type="checkbox" name="aktiv" value="1" @checked($kategorie->aktiv) aria-label="aktiv"></td>
                                <td>
                                    <form id="kat-{{ $kategorie->id }}" method="post" action="{{ route('einstellungen.kategorien.update', $kategorie->id) }}">
                                        @csrf
                                        @method('put')
                                        <button type="submit" class="btn btn-sm btn-secondary">speichern</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><input form="kat-neu" type="text" name="label" class="form-control form-control-sm" required placeholder="Neue Kategorie" aria-label="Bezeichnung neue Kategorie"></td>
                            <td><input form="kat-neu" type="text" name="gruppe" class="form-control form-control-sm" required value="Weiteres" list="gruppen" aria-label="Gruppe"></td>
                            <td><input form="kat-neu" type="number" name="groesse_von" class="form-control form-control-sm" min="0" max="999" aria-label="Größe von"></td>
                            <td><input form="kat-neu" type="number" name="groesse_bis" class="form-control form-control-sm" min="0" max="999" aria-label="Größe bis"></td>
                            <td><input form="kat-neu" type="number" name="sortierung" class="form-control form-control-sm" min="0" value="{{ ($kategorien->max('sortierung') ?? 0) + 10 }}" aria-label="Reihenfolge"></td>
                            <td></td>
                            <td>
                                <form id="kat-neu" method="post" action="{{ route('einstellungen.kategorien.store') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">anlegen</button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <datalist id="gruppen">
                        @foreach ($kategorien->pluck('gruppe')->unique() as $gruppe)
                            <option value="{{ $gruppe }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
        </div>
    </div>
@endsection
