@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Vorhandene Mailvorlagen
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($Vorlagen AS $Vorlage)
                                <a class="list-group-item @if (!$Vorlage->deleteable) bg-warning @endif" href="{{url('mailvorlagen/'.$Vorlage->id."/edit")}}">

                               <span class="pull-left btn btn-sm">
                                       <i class="font-icon font-icon-pencil"></i>
                               </span>
                                    <span class="ml-2">
                                   {{$Vorlage->name}}
                               </span>
                                </a>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        neue Mailvorlage
                    </div>
                    <div class="card-body">
                        <form class="form-horizontal" method="post" action="{{url('mailvorlagen')}}">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <input class="form-control" name="name">
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-success">speichern</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop