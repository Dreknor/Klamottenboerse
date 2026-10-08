@extends('layouts.app')

@section('content')
    <div class="container-fluid" style="max-width: 640px;">
        <div class="card">
            <div class="card-header">
                <h4>Vorfall zu Verkäufer erfassen</h4>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <p class="text-muted small">
                    Z. B. Kisten nicht gebracht, Abgabe- oder Abholtermin verpasst, defekte Ware.
                    Bei zu vielen Vermerken erhält der Verkäufer keine VK-Nummer mehr automatisch.
                </p>
                <form method="post" action="{{ route('vermerke.store') }}">
                    @include('vermerke._form', ['vknummer' => $vknummer, 'quelle' => $quelle, 'prefix' => 'vermerkSeite'])
                    <button type="submit" class="btn btn-warning btn-block">Vermerk speichern</button>
                </form>
            </div>
        </div>
    </div>
@endsection
