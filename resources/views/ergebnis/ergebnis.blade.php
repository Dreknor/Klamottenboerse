@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @foreach($vknummernFreigegeben as $vknummer )
            <div class="card mb-3">
                <div class="card-header">
                    <h3>
                        Ergebnis für VK-Nummer {{ $vknummer->vknummer }} zur Klamottenbörse am {{ $vknummer->klamottenboerse->datum->format('d.m.Y') }}
                    </h3>
                </div>
                <div class="card-body">
                    <p>
                        Hier sehen Sie die Artikel, die verkauft wurden.
                    </p>

                    <div class="table-md-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        Artikel
                                    </th>
                                    <th>
                                        Preis
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vknummer->verkaufteArtikel()->withoutGlobalScopes()->get()->sortBy('artikelnummer') as $artikel)
                                    <tr>
                                        <td>
                                            {{ $artikel->artikelnummer }}
                                        </td>
                                        <td>
                                            {{ number_format($artikel->betrag, 2, ',', '.') }} €
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-info text-white">
                                <tr>
                                    <th>
                                        Summe:
                                    </th>
                                    <th>
                                        {{ number_format($vknummer->verkaufteArtikel()->withoutGlobalScopes()->sum('betrag'), 2, ',', '.') }} €
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        Davon 25%:
                                    </th>
                                    <th>
                                        {{ number_format($vknummer->verkaufteArtikel()->withoutGlobalScopes()->sum('betrag') / 100 * 25, 2, ',', '.') }} €
                                    </th>
                                </tr>
                            <tr>
                                <th>
                                    Auszahlungsbetrag:
                                </th>
                                <th>
                                    {{ number_format($vknummer->verkaufteArtikel->sum('betrag') - $vknummer->verkaufteArtikel->sum('betrag') / 100 * 25, 2, ',', '.') }} €
                                </th>
                            </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
