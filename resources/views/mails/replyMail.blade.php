@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <form action="{{url('mail/reply/send')}}" method="post" class="form-horizontal">
                @csrf
                @method('put')
                <div class="card-header">
                    <h4>Antwort an {{$Mail->getFrom()[0]->full}}</h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label for="email" class="control-label col-sm-2">An:</label>
                        <input type="text" name="email" value="{{$Mail->getFrom()[0]->mail}}" class="form-control col-sm-10">
                    </div>
                    <div class="form-group row">
                        <label for="betreff" class="control-label  col-sm-2">Betreff:</label>
                        <input type="text" name="betreff" value="Re: {{$Mail->getSubject()}}" placeholder="Betreff" class="form-control col-sm-10" autofocus>
                    </div>
                </div>
                <div class="card-body">
                        <textarea id="text" name="text" class="form-control">
                            <br>
                            <hr>
                            {{$Mail->getTextBody()}}
                        </textarea>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn">Nachricht versenden</button>
                    <a href="{{ url()->previous() }}" class="btn btn-grey">verwerfen</a>
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
            selector: '#text',
            toolbar:'bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, alignjustify, styleselect, formatselect, fontselect, fontsizeselect, bullist, numlist, image, table, outdent, indent, blockquote, undo, redo, removeformat, autolink',
            menubar: false,
            height: 300,
            table_default_attributes: {
                border: '0'
            }
        });
    </script>
@stop
