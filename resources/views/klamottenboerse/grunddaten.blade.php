@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Daten der aktuellen Klamottenbörse</p>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        Datum der Klamottenbörse:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="#" id="dob" data-type="combodate" data-value="{{ $Klamottenboerse->datum }}" data-format="YYYY-MM-DD" data-viewformat="DD.MM.YYYY" data-template="D . MMM . YYYY" data-pk="1"  data-title="Wann findet die Klamottenbörse statt?"></a>                                                {{ $Interessent->anrede }}
                                            </a>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        Name:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="x" class="pUpdate" id="nachname" data-type="text" data-pk="{{ $Interessent->id }}" data-title="Nachname bearbeiten">
                                                {{ $Interessent->nachname }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Vorame:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="x" class="pUpdate" id="vorname" data-type="text" data-pk="{{ $Interessent->id }}" data-title="Vorname bearbeiten">
                                                {{ $Interessent->vorname }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        E-Mail:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="x" class="pUpdate" data-name="mail" data-type="text" data-pk="{{ $Interessent->id }}" data-title="E-Mail bearbeiten">
                                                {{ $Interessent->mail }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Telefon:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                        <p>
                                            <a href="x" class="pUpdate" id="telefon"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Telefon bearbeiten">
                                                {{ $Interessent->telefon }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Anschrift:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                        <p>
                                            <a href="x" class="pUpdate" id="straße"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Straße bearbeiten">
                                                {{ $Interessent->straße }}
                                            </a>
                                            <a href="x" class="pUpdate" id="hausnummer"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Hausnummer bearbeiten">
                                                {{ $Interessent->hausnummer }}
                                            </a>
                                            <br />
                                            <a href="x" class="pUpdate" id="plz"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Postleitzahl bearbeiten">
                                                {{ $Interessent->plz }}
                                            </a>
                                            <a href="x" class="pUpdate" id="ort"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Ort bearbeiten">
                                                {{ $Interessent->ort }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Mitarbeiter:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                        <p>
                                            @if($Interessent->mitarbeiter  == 1)
                                                <a href="x" class="pUpdate" id="mitarbeiter" data-value="1" data-type="select"  data-pk="{{ $Interessent->id }}" data-title="Ist dies ein Mitarbeiter?">
                                                    ja
                                                    @else
                                                        <a href="x" class="pUpdate" id="mitarbeiter" data-value="0" data-type="select"  data-pk="{{ $Interessent->id }}" data-title="Ist dies ein Mitarbeiter?">
                                                            nein
                                                            @endif
                                                        </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Kinderhaus:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>

                                        @if($Interessent->kinderhaus  == 1)
                                            <a href="x" class="pUpdate" id="kinderhaus"  data-value="1" data-type="select" data-pk="{{ $Interessent->id }}" data-title="Eine Familie aus dem Kinderhaus?">
                                                ja
                                                @else
                                                    <a href="x" class="pUpdate" id="kinderhaus"  data-value="0" data-type="select" data-pk="{{ $Interessent->id }}" data-title="Eine Familie aus dem Kinderhaus?">
                                                        nein
                                                        @endif
                                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <p>Historie</p>
                            </div>
                            <div class="panel-body">

                            </div>
                            <div class="panel-footer">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">

        $(function() {
            //edit form style - popup or inline
            $.fn.editable.defaults.mode = 'popup';

            $.fn.editable.defaults.params = function (params) {
                params._token = $("#_token").data("token");
                return params;
            };

            $('#mitarbeiter').editable({
                limit: 1,
                source: [
                    {value: '1', text: 'ja'},
                    {value: '0', text: 'nein'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('#anrede').editable({
                limit: 1,
                source: [
                    {value: 'Familie', text: 'Familie'},
                    {value: 'Herr', text: 'Herr'},
                    {value: 'Frau', text: 'Frau'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('#kinderhaus').editable({
                limit: 1,
                source: [
                    {value: '1', text: 'ja'},
                    {value: '0', text: 'nein'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('.pUpdate').editable({
                validate: function (value) {
                    if ($.trim(value) == '')
                        return 'Eingabe wird benötigt.';
                },

                url: '{{URL::to("/")}}/edit-Interessent',
                title: 'Bearbeiten',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            })
        })
    </script>

@endsection


