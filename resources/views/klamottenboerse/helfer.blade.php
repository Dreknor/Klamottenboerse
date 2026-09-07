@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>Helfer</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>offene Termine – Schicht-Baukasten</h4>
                        <p>noch benötigte Helfer, gruppiert nach Bereich:</p>
                        @foreach(\App\Model\Appointment::BEREICHE as $bereichKey => $bereichLabel)
                            <h5 class="mt-3">{{ $bereichLabel }}</h5>
                            <ul class="list-group mb-3">
                                @forelse($termine->where('bereich', $bereichKey) as $termin)
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-5">
                                                {{ $termin->date_start->format('d.m.Y H:i')}} - {{$termin->date_end->format('H:i')}}
                                            </div>
                                            <div class="col-md-5">
                                                {{ $termin->beschreibung }}
                                            </div>
                                            <div class="col-md-2">
                                                <form action="{{ route('appointment.destroy', $termin->id)  }}" method="post">
                                                    @method('DELETE')
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $termin->id }}">

                                                    <button type="submit" class="btn btn-danger">löschen</button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">keine offenen Termine</li>
                                @endforelse
                            </ul>
                        @endforeach
                        <hr>
                        <div class="card">
                            <div class="card-header">
                                <h4>neuer Termin</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('appointment.store') }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="date_start">Start</label>
                                        <input type="datetime-local" name="date_start" class="form-control" value="{{old('date_start')}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="date_end">Ende</label>
                                        <input type="datetime-local" name="date_end" class="form-control" value="{{old('date_end')}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="beschreibung">Beschreibung</label>
                                        <input type="text" name="beschreibung" class="form-control"  value="{{old('beschreibung')}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="bereich">Bereich</label>
                                        <select name="bereich" class="form-control">
                                            @foreach(\App\Model\Appointment::BEREICHE as $bereichKey => $bereichLabel)
                                                <option value="{{ $bereichKey }}" {{ old('bereich') == $bereichKey ? 'selected' : '' }}>{{ $bereichLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select name="anzahl" class="form-control">
                                            <option value="1">1 Helfer</option>
                                            <option value="2">2 Helfer</option>
                                            <option value="3">3 Helfer</option>
                                            <option value="4">4 Helfer</option>
                                            <option value="5">5 Helfer</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">speichern</button>
                                </form>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <h4>eingetragene Helfer</h4>
                        <p>Folgende Personen helfen mit</p>
                        <ul class="list-group">
                            @foreach($helfer as $helfer)
                                <li class="list-group-item">
                                    {{ \App\Model\Appointment::BEREICHE[$helfer->appointment->bereich] ?? $helfer->appointment->bereich }}:
                                    {{ $helfer->appointment->date_start->format('d.m.Y H:i') }} - {{ $helfer->appointment->date_end->format('H:i') }} Uhr: {{$helfer->appointment->beschreibung}} <br>
                                   {{ $helfer->name }} ( {{ $helfer->mail }} / {{ $helfer->telefon }})
                                </li>
                            @endforeach
                        </ul>
                    </div>
            </div>
        </div>
    </div>
    </div>
@endsection
