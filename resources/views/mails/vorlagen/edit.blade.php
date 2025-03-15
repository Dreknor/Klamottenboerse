@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                Bearbeite Mailvorlage {{$Vorlage->name}}
                @if ($Vorlage->deleteable)
                    <div class="pull-right">
                        <form method="post" action="{{url('mailvorlagen/'.$Vorlage->id)}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="font-icon font-icon-trash"></i>
                            </button>
                        </form>
                    </div>
                @endif

            </div>
            @if (count($errors)>0)
                <div class="card-header">
                    @foreach ($errors->all() as $message)
                        <p class="alert alert-danger">
                            {{$message}}
                        </p>
                    @endforeach
                </div>
            @endif
            <form class="form-horizontal" method="post" action="{{url('mailvorlagen/'.$Vorlage->id)}}">
                @csrf
                @method('put')
                <div class="card-body">
                    <input class="form-control" name="betreff" value="{{$Vorlage->betreff}}" required>
                </div>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-12 col-md-10">
                            <div class="row">
                                <div class="col-12">
                                    <textarea id="html" class="form-control html" name="html" required>
                                        {{$Vorlage->html}}
                                    </textarea>
                                </div>
                            </div>
                            <div class="row mt-1">

                            <div class="col-12">
                                    <textarea id="text" class="form-control text" name="text" style="height: 250px" required>
                                        {{$Vorlage->text}}
                                    </textarea>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <span>Umgewandelt werden:</span>
                            <ul class="list-group small">
                                <li class="list-group-item">ANREDE</li>
                                <li class="list-group-item">LIEBE</li>
                                <li class="list-group-item">VORNAME</li>
                                <li class="list-group-item">NACHNAME</li>
                                <li class="list-group-item">EMAIL</li>
                                <li class="list-group-item">VKNUMMER</li>
                                <li class="list-group-item">ABSENDER</li>
                                <li class="list-group-item">DATUM</li>
                                <li class="list-group-item">ANMELDUNG</li>
                                <li class="list-group-item">ANNAHME</li>
                                <li class="list-group-item">ORT</li>
                                <li class="list-group-item">ADRESSE</li>
                                <li class="list-group-item">ANLIEFERUNG_AB</li>
                                <li class="list-group-item">ANLIEFERUNG_BIS</li>
                                <li class="list-group-item">ABHOLUNG_AB</li>
                                <li class="list-group-item">ABHOLUNG_BIS</li>
                                <li class="list-group-item">MAXTEILE</li>
                                <li class="list-group-item">VERKAEUFELINK</li>
                            </ul>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-block btn-success">
                        speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="{{asset('css/mail.css')}}">
    <link rel="stylesheet" href="{{asset('css/editor.css')}}">
@endsection

@section('js')
    <script src='{{asset('js/tinymce/tinymce.min.js')}}'></script>
    <script src='{{asset('js/tinymce/langs/de.js')}}'></script>
    <script>
        tinymce.init({
            plugins: "table, autolink, image, lists, textcolor",
            selector: '#html',
            toolbar:'bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, alignjustify, styleselect, formatselect, fontselect, fontsizeselect, forecolor, backcolor, bullist, numlist, image, table, outdent, indent, blockquote, undo, redo, removeformat, autolink',
            menubar: false,
            height: 300,
            table_default_attributes: {
                border: '0'
            }
        });
    </script>
@stop
