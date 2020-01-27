@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <form action="{{url('vknummern/vergeben')}}" method="post">
                    {{csrf_field()}}
                    {{method_field('put')}}
                    <div class="card">
                        <div class="card-header">
                            An wen soll die Verkäufernummer <b><input type="hidden" name="NummernID" value="{{$Nummer->id}}">{{ $Nummer->vknummer}}</b> vergeben werden?<br>
                            <p class="subtitle">Der Verkäufer wird per Mail informiert</p>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table id="InteressentenTable" class="table table-striped table-bordered" >
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Verkäufer</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if ($Nummer->reserviert_fuer != "")
                                        <tr class="table-warning">
                                            <td>
                                                <input type="radio" name="InteressentID" value="{{$Nummer->reserviert_fuer_Interessent->id}}">
                                            </td>
                                            <td>
                                                {{$Nummer->reserviert_fuer_Interessent->vorname}} {{$Nummer->reserviert_fuer_Interessent->nachname}}
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach($Interessenten AS $Interessent)
                                        <tr>
                                            <td>
                                                <input type="radio" name="InteressentID" value="{{$Interessent->id}}">
                                            </td>
                                            <td>
                                                {{$Interessent->nachname}},  {{$Interessent->vorname}}
                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>

                            </div>
                        </div>

                        <div class="card-footer">
                            <button class="btn btn-success" type="submit" name="anlegen" >Nummer vergeben und Verkäufer informieren</button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>

@endsection
@section('js')
    <script src="{{asset('js/lib/datatables-net/datatables.min.js')}}"></script>
    <script src="{{asset('js/lib/datatables-net/buttons-1.2.0/js/dataTables.buttons.js')}}"></script>
    <script src="{{asset('js/lib/datatables-net/buttons-1.2.0/js/buttons.bootstrap4.min.js')}}"></script>
    <script>
        $(function() {
            var table = $('#InteressentenTable').DataTable();
        });
    </script>
@stop