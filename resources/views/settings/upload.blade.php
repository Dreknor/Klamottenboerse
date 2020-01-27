@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                Umsätz aus der Kassenabrechnung importieren
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{url('import')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="import" name="import">
                                <label class="custom-file-label" for="import">Datei wählen</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col">
                            <button type="submit" class="btn btn-block btn-success">Importieren</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script type="application/javascript">
        $('input[type="file"]').change(function(e){
            var fileName = e.target.files[0].name;
            $('.custom-file-label').html(fileName);
        });
    </script>
@stop