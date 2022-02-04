@extends('layouts.app')

@section('content')
     <div class="container-fluid">
            <div class="card ">
                <div class="card-header">
                    <p>
                        <a class="glyphicon glyphicon-menu-left pull-right" href="{{ url("/Nummern")  }}"> zurück</a>
                    </p>
                </div>
                <div class="card-body">
                    <form action="{{url('vknummern')}}" method="post" class="form-horizontal">
                        {{csrf_field()}}
                        <p>Welche Verkäufernummer soll neu angelegt werden?</p>
                        <p><input class="form-control" name="vknummer" type="number" min="200" max="600" autofocus>
                        <p><button class="btn btn-success" type="submit" name="anlegen" >anlegen</button></p>
                    </form>
                </div>
            </div>

        </div>
@endsection