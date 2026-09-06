@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 640px;">
    <div class="app-card p-4 mt-4 text-center">
        <h3 class="mb-3">Platz bestätigt!</h3>
        <p>Deine VK-Nummer {{ $vknummer->vknummer }} wurde dir zugewiesen. Wir haben dir alle weiteren Informationen per E-Mail zugesendet.</p>
    </div>
</div>
@endsection
