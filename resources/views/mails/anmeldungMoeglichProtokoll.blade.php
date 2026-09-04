@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <header class="section-header">
            <div class="tbl">
                <div class="tbl-row">
                    <div class="tbl-cell">
                        <h2 id="Ueberschrift">Mail-Protokoll: Anmeldung möglich</h2>
                        <div class="subtitle">
                            @if ($Klamottenboerse)
                                Klamottenbörse {{ $Klamottenboerse->datum->format('d.m.Y') }}
                            @else
                                Keine Klamottenbörse gefunden
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <span class="label label-pill label-success">Versendet: {{ $AnzahlGesendet }}</span>
                    </div>
                    <div class="col">
                        <span class="label label-pill label-warning">Wartet (Rate-Limit): {{ $AnzahlOffen }}</span>
                    </div>
                    <div class="col">
                        <span class="label label-pill label-danger">Fehlgeschlagen: {{ $AnzahlFehlgeschlagen }}</span>
                    </div>
                    <div class="col text-right">
                        @if ($AnzahlOffen + $AnzahlFehlgeschlagen > 0)
                            <form action="{{ url('mail-protokoll/anmeldung-moeglich/resend-all') }}" method="post" onsubmit="return confirm('Alle noch nicht zugestellten Mails erneut versenden?');">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    Alle nicht zugestellten Mails erneut senden
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-s table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Status</th>
                                <th>Versendet am</th>
                                <th>Fehler / Log</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($MailLogs as $mailLog)
                                <tr>
                                    <td>
                                        @if ($mailLog->interessent)
                                            <a href="{{ url('interessent').'/'.$mailLog->interessent->id }}" class="link">
                                                {{ $mailLog->interessent->vorname }} {{ $mailLog->interessent->nachname }}
                                            </a>
                                        @else
                                            <em>Interessent gelöscht</em>
                                        @endif
                                    </td>
                                    <td>{{ $mailLog->email }}</td>
                                    <td>
                                        @if ($mailLog->status === 'sent')
                                            <span class="label label-success">versendet</span>
                                        @elseif ($mailLog->status === 'failed')
                                            <span class="label label-danger">fehlgeschlagen</span>
                                        @else
                                            <span class="label label-warning">wartet</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $mailLog->versendet_at ? $mailLog->versendet_at->format('d.m.Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        {{ $mailLog->fehler }}
                                    </td>
                                    <td>
                                        @if ($mailLog->status !== 'sent')
                                            <form action="{{ url('mail-protokoll/anmeldung-moeglich/'.$mailLog->id.'/resend') }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary-outline">
                                                    erneut senden
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Für diese Klamottenbörse wurden noch keine Anmeldungs-Mails eingeplant.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@stop
