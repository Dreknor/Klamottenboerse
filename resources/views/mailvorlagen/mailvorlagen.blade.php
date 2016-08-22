@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                       <div class="row">
                           <div class="col-lg-11">
                               Mailvorlagen
                           </div>
                           <div class="col-lg-1">
                                   <a href="{{ url('Mailvorlagen/new') }}" class="btn btn-xs btn-success glyphicon glyphicon-plus" title="neue Vorlage anlegen"></a>
                           </div>
                       </div>
                    </div>
                    <div class="panel-body">
                        @if(count($Vorlagen) > 0)
                            <div class="list-group">
                                @foreach($Vorlagen AS $Vorlage)
                                    <a href="#" class="list-group-item clearfix">
                                            {{ $Vorlage->name }}
                                            <span class="pull-right">
                                                <button class="btn btn-xs btn-default">
                                                    <span class="glyphicon glyphicon-pencil"></span>
                                                </button>

                                                <button class="btn btn-xs btn-danger">
                                                  <span class="glyphicon glyphicon-trash"></span>
                                                </button>
                                            </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p>Es sind keine Mailvorlagen angelegt</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection