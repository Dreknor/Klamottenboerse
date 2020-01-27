@extends('layouts.app')

@section('content')
    <div class="container-fluid">
                <div class="card ">
                    <div class="card-header">
                        <h4 class="card-title">
                            Neuen Interressenten anlegen
                        </h4>
                    </div>
                    @if (count($errors)>0)
                        <div class="card-body bg-warning">
                            @foreach($errors->all() as $error)
                                {{$error}}<br>
                            @endforeach
                        </div>
                    @endif
                    <div class="card-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('interessenten') }}">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="col">Anrede</label>
                                        <div class="col">
                                            <select name="anrede" class="form-control">
                                                <option value="Familie">Familie</option>
                                                <option value="Herr">Herr</option>
                                                <option value="Frau">Frau</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col">Vorname</label>
                                        <div class="col">
                                            <input name="vorname" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col">Nachname</label>
                                        <div class="col">
                                            <input name="nachname" class="form-control"
                                            @if ($name != "")
                                                value="{{$name}}"
                                            @endif>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col">Telefon</label>
                                        <div class="col">
                                            <input name="telefon" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col">Handy</label>
                                        <div class="col">
                                            <input name="handy" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="col">E-Mail</label>
                                        <div class="col">
                                            <input name="mail" class="form-control"
                                                   @if ($mail != "")
                                                        value="{{$mail}}"
                                                    @endif>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="col-12 col-md-6">Kinderhaus</label>
                                        <div class="col-12 col-md-6">
                                            <input type="checkbox" name="kinderhaus" value="1">Ja
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label class="col-12 col-md-6">Mitarbeiter</label>
                                        <div class="col-12 col-md-6">
                                            <input type="checkbox" name="mitarbeiter" value="1">Ja
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                            <button type="submit" class="btn btn-block btn-success ">
                                                Anlegen
                                            </button>
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>


@endsection