@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3>
                    Verkaufte Artikel zur Klamottenbörse am {{ $klamottenboerse->datum->format('d.m.Y') }}
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
                            @foreach($vknummer->verkaufteArtikel->sortBy('artikelnummer') as $artikel)
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
                                    {{ number_format($vknummer->verkaufteArtikel->sum('betrag'), 2, ',', '.') }} €
                                </th>
                            </tr>
                            <tr>
                                <th>
                                    Davon {{$einbehalt}}%:
                                </th>
                                <th>
                                    {{ number_format($vknummer->verkaufteArtikel->sum('betrag') / 100 * $einbehalt, 2, ',', '.') }} €
                                </th>
                            </tr>
                        <tr>
                            <th>
                                Auszahlungsbetrag:
                            </th>
                            <th>
                                {{ number_format($vknummer->verkaufteArtikel->sum('betrag') - $vknummer->verkaufteArtikel->sum('betrag') / 100 * $einbehalt, 2, ',', '.') }} €
                            </th>
                        </tr>
                        </tfoot>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
