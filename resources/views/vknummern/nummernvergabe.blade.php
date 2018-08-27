@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8 col-lg-offset-2">
            <form action="{{url('Nummern/vergeben')}}" method="post">
                {{csrf_field()}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Welche Verkäufernummer soll an <b>{{ $Interessent->vorname}} {{ $Interessent->nachname}}</b> vergeben werden?
                        <input type="hidden" name="InteressentenID" value="{{$Interessent->id}}">
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="vknummer">Nummer auswählen:</label>
                            <div class=" table-responsive">
                                <table id="NummernTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nachname</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($Nummern AS $Nummer)
                                        <tr>
                                            <td>
                                                <input type="radio" name="NummernID" value="{{$Nummer->id}}">
                                            </td>
                                            <td>
                                                {{$Nummer->vknummer}}
                                            </td>
                                            <td>
                                                @if (count($Nummer->bisherigeVerkaeufer) > 0)
                                                    @foreach($Nummer->bisherigeVerkaeufer AS $Verkaeufer)
                                                        @if ($Verkaeufer->vergeben_an != "")
                                                            <span class="label label-warning">{{$Verkaeufer->vergeben_an_Interessent->nachname}}, {{$Verkaeufer->vergeben_an_Interessent->vorname}}</span>
                                                        @endif

                                                    @endforeach
                                                @endif

                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody>
                                </table>

                        </div>


                    </div>

                    <div class="panel-footer">
                        <button class="btn btn-success" type="submit" name="anlegen" >Nummer vergeben und Verkäufer informieren</button>
                    </div>
                </div>

                </div>
            </form>
        </div>
    </div>


    <script type="text/javascript">


        $(document).ready(function(){
            $('#NummernTable').DataTable({
                paging: false
            });
        });


    </script>
@endsection