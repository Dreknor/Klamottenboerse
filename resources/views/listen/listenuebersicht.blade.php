@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 col-lg-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Welche Liste soll erstellt werden?</div>
                <div class="panel-body">
                    <div class="list-group">
                            <a href="{{ url('Listen/vknummern') }}" target="_new" class="list-group-item">Verkäufernummern</a>
                            <a href="{{ url('Listen/belehrung') }}" target="_new" class="list-group-item">Verkäufer-Belehrung</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection