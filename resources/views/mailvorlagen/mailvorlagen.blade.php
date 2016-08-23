@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                       <div class="row">
                           <div class="col-lg-11">
                               Mailvorlagen
                           </div>
                           <div class="col-lg-1">
                                   <a href="{{ url('Mailvorlagen/new') }}" class="btn btn-xs btn-success glyphicon glyphicon-plus" title="neue Vorlage anlegen"></a>
                           </div>
                       </div>
                    </div>
                    <div class="panel-body">
                        @if(count($Vorlagen) > 0)
                            <div class="list-group">
                                @foreach($Vorlagen AS $Vorlage)
                                    <a href="#" class="list-group-item clearfix">
                                            {{ $Vorlage->name }}
                                            <span class="pull-right">
                                                <button class="btn btn-xs btn-default">
                                                    <span class="glyphicon glyphicon-pencil"></span>
                                                </button>

                                                <button class="btn btn-xs btn-danger" type="button"
                                                        data-toggle="modal"
                                                        data-target="#LoescheVorlage"
                                                        data-name="{{$Vorlage->name}}"
                                                        data-vorlage = "{{$Vorlage->id}}">
                                                  <span class="glyphicon glyphicon-trash"></span>
                                                </button>
                                            </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p>Es sind keine Mailvorlagen angelegt</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="LoescheVorlage" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title">Vorlage löschen</h4>

                </div>
                <div class="modal-body">
                    <p class="modal-inhalt">Soll die Vorlage wirklich gelöscht werden?</p>
                    <p><b id="VorlagenName"></b></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="loeschen" action="{{ url('Mailvorlagen/loeschen')}}">
                        {{csrf_field()}}
                        {{method_field('delete')}}
                        <input type="hidden" name="VorlagenID" id="FormVorlagenID" value="">
                    </form>
                    <button type="submit" form="loeschen" class="btn btn-danger">Vorlage löschen</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">



        $('#LoescheVorlage').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var VorlageID = button.data('vorlage')
            var VorlageName = button.data('name')

            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('#VorlagenName').text(VorlageName)
            modal.find('#FormVorlagenID').val(VorlageID)


        })







    </script>
@endsection