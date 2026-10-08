@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Verkäufer-Reputation</h3>
                <p class="text-muted small mb-0">
                    Es zählen Vermerke der letzten {{ \App\Model\Einstellung::zahl('reputation_zeitraum_monate') }} Monate.
                    Ab {{ \App\Model\Einstellung::zahl('reputation_sperre_ab') }} Punkten ist keine automatische Nummernvergabe
                    (Warteliste-Nachrücken) mehr möglich – die Nummer kann dann nur noch händisch vergeben werden.
                </p>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <a class="btn btn-warning btn-sm mb-3" href="#vermerkModal" data-toggle="modal">Vorfall erfassen</a>
                <a class="btn btn-secondary btn-sm mb-3" href="{{ route('einstellungen.reputation') }}">Punkte &amp; Schwellen einstellen</a>

                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Verkäufer</th>
                        <th>Punkte</th>
                        <th>Status</th>
                        <th>Vermerke</th>
                        <th>Vergabemodus</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($interessenten as $interessent)
                        <tr>
                            <td>
                                <a href="{{ url('interessent/'.$interessent->id) }}">{{ $interessent->nachname }}, {{ $interessent->vorname }}</a>
                            </td>
                            <td>{{ $interessent->reputationPunkte() }}</td>
                            <td>
                                @include('vermerke._badge', ['interessent' => $interessent])
                                @if ($interessent->nur_manuelle_vergabe === false)
                                    <span class="badge badge-success">freigegeben</span>
                                @endif
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach ($interessent->vermerke as $vermerk)
                                        <li>
                                            {{ $vermerk->created_at->format('d.m.Y') }}:
                                            <b>{{ $vermerk->typLabel }}</b> ({{ $vermerk->punkte }} Pkt.)
                                            @if ($vermerk->vknummer) – VK {{ $vermerk->vknummer->vknummer }} @endif
                                            @if ($vermerk->bemerkung) – {{ $vermerk->bemerkung }} @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                @include('vermerke._vergabemodus', ['interessent' => $interessent])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Bisher keine Vermerke erfasst.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('vermerke._modal', ['quelle' => \App\Model\VerkaeuferVermerk::QUELLE_VERWALTUNG])
@endsection
