@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <form action="{{url('mail/'.$Interessent->id.'/send')}}" method="post" class="form-horizontal">
                @csrf
                @method('put')
                <div class="card-header">
                    <h4>Neue E-Mail an {{$Interessent->vorname}} {{$Interessent->nachname}}</h4>
                </div>
                <div class="card-body">
                    <input type="text" name="betreff" value="@if (isset($Vorlage)) {{$Vorlage->betreff}} @endif" placeholder="Betreff" class="form-control" autofocus>
                </div>
                <div class="card-body">
                        <textarea id="html" name="text" class="form-control">
                            {!! $Vorlage->text !!}
                        </textarea>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn">Nachricht versenden</button>
                    <a href="{{url('interessent/'.$Interessent->id)}}" class="btn btn-grey">verwerfen</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('css/mail.css')}}">
    <link rel="stylesheet" href="{{asset('css/editor.css')}}">
@endsection

@section('js')
    <script src='{{asset('js/tinymce/tinymce.min.js')}}'></script>
    <script src='{{asset('js/tinymce/langs/de.js')}}'></script>
    <script>
        tinymce.init({
            plugins: "table, autolink, image, lists",
            selector: '#html',
            toolbar:'bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, alignjustify, styleselect, formatselect, fontselect, fontsizeselect, bullist, numlist, image, table, outdent, indent, blockquote, undo, redo, removeformat, autolink',
            menubar: false,
            height: 300,
            table_default_attributes: {
                border: '0'
            }
        });
    </script>
@stop
