@extends('layouts.app')

@section('content')
<div class="container-fluid">
            <form action="{{url('vknummern/vergeben')}}" method="post">
                {{csrf_field()}}
                {{method_field('put')}}
                <div class="card">
                    <div class="card-header">
                        Welche Verkäufernummer soll für <b><input type="hidden" name="InteressentID" value="{{$Interessent->id}}">{{ $Interessent->vorname}} {{ $Interessent->nachname}}</b> vergeben werden?<br>
                        <p class="subtitle">Der Verkäufer wird per Mail informiert</p>
                    </div>
                    <div class="card-body">
                        <div class=" table-responsive">
                            <table id="NummernTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Nummer</th>
                                    <th>letzte Verkäufer</th>
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
                                                @foreach($Nummer->bisherigeVerkaeufer->take(5) AS $Verkaeufer)
                                                    @if ($Verkaeufer->vergeben_an != "")
                                                        <span class="label  @if ($Verkaeufer->vergeben_an == $Interessent->id) label-success @else label-light-grey @endif">
                                                            @if (!is_object($Verkaeufer->vergeben_an_Interessent))
                                                                gelöscht
                                                            @else
                                                                {{$Verkaeufer->vergeben_an_Interessent->nachname}}, {{$Verkaeufer->vergeben_an_Interessent->vorname}}
                                                            @endif
                                                        </span>
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

                    <div class="card-footer">
                        <button class="btn btn-success" type="submit" name="anlegen" >Nummer vergeben und Verkäufer informieren</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection