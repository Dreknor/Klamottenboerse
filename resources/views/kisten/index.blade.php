@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Kisten Check-in / Check-out</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-5">
                        <h4>Check-in: Kisten abgeben</h4>
                        <form action="{{ route('kisten.store') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="vknummer_id">Verkäufer / VK-Nummer</label>
                                <select name="vknummer_id" class="form-control">
                                    @foreach($vknummern as $vk)
                                        <option value="{{ $vk->id }}">
                                            {{ $vk->vknummer }}
                                            @if($vk->vergeben_an_Interessent)
                                                - {{ $vk->vergeben_an_Interessent->vorname }} {{ $vk->vergeben_an_Interessent->nachname }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="anzahl">Anzahl Kisten</label>
                                <input type="number" name="anzahl" min="1" max="50" value="1" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="bemerkung">Bemerkung</label>
                                <input type="text" name="bemerkung" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Kisten einchecken</button>
                        </form>
                    </div>

                    <div class="col-md-7">
                        <h4>Erfasste Kisten</h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>VK-Nummer</th>
                                <th>Kiste #</th>
                                <th>Status</th>
                                <th>Abgegeben</th>
                                <th>Abgeholt</th>
                                <th>QR-Code</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($kisten as $kiste)
                                <tr>
                                    <td>{{ $kiste->vknummer->vknummer ?? '-' }}</td>
                                    <td>{{ $kiste->kistennummer }}</td>
                                    <td>
                                        @if($kiste->istAbgeholt())
                                            <span class="badge badge-success">abgeholt</span>
                                        @else
                                            <span class="badge badge-warning">abgegeben</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($kiste->abgegeben_at)->format('d.m.Y H:i') }}</td>
                                    <td>{{ optional($kiste->abgeholt_at)->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode(route('kisten.scan', $kiste->qr_token)) }}" alt="QR-Code Kiste {{ $kiste->kistennummer }}" width="60" height="60">
                                    </td>
                                    <td>
                                        @if(!$kiste->istAbgeholt())
                                            <form action="{{ route('kisten.checkout', $kiste->id) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Check-out</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
