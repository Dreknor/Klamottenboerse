@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10" >
                <div class="alert alert-danger " role="alert">
                    <div class="container">
                        <div class="row">
                             <p><strong>Warung! Soll der Interessent {{ $Interessent->vorname }} {{ $Interessent->nachname }} vollständig aus der Datenbank gelöscht werden?</strong><br /><br /></p>
                            </div>
                        </div>
                    <div class="container">
                            <div class="row">
                                <div class="col-md-2" >
                                     <form method="post" action="{{action('InteressentenController@destroy', [$Interessent->id])}}">
                                       {{method_field('delete')}}
                                       <input type="submit" class="btn btn-danger" value="Interessent löschen">
                                       {{ csrf_field() }}
                                   </form>
                                </div>

                                <div class="col-md-2" >
                                    <a class="btn btn-success" href="{{url("/Interessent/$Interessent->id")}}">Abbrechen</a>
                                </div>

                       </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection