@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-7 col-md-offset-1">
            <div class="panel-default">
                <div class="panel-heading">
                    Neuen Interressenten anlegen
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/Anlegen') }}">
                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="col-md-3">Anrede</label>

                            <div class="col-md-6">
                                <select name="anrede" class="form-control">
                                    <option value="Familie">Familie</option>
                                    <option value="Herr">Herr</option>
                                    <option value="Frau">Frau</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3">Vorname</label>

                            <div class="col-md-6">
                                <input name="vorname" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3">Nachname</label>

                            <div class="col-md-6">
                                <input name="nachname" class="form-control">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-md-3">Telefon</label>

                            <div class="col-md-6">
                                <input name="telefon" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Handy</label>

                            <div class="col-md-6">
                                <input name="handy" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">E-Mail</label>

                            <div class="col-md-6">
                                <input name="mail" class="form-control">
                            </div>
                        </div>

                      <div class="form-group">
                            <label class="col-md-3">Kinderhaus</label>

                            <div class="col-md-6">
                                <input type="checkbox" name="kinderhaus" value="1">Ja
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3">Mitarbeiter</label>

                            <div class="col-md-6">
                                <input type="checkbox" name="mitarbeiter" value="1">Ja
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-success">
                                   Anlegen
                                </button>


                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection