@extends('layouts.app')

@section('content')

    <div class="container">

        <form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;" action="{{ url('import') }}" class="form-horizontal" method="post" enctype='multipart/form-data'>
            {{ csrf_field() }}
            <input type="file" name="import_file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
            <button class="btn btn-primary">Import File</button>
        </form>
    </div>
@endsection