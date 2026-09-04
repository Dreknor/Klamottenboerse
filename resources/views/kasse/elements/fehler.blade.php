 @if(isset($Fehler) and $Fehler != "")
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1" >
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Warung! </strong>
                        {{ $Fehler }}
                    </div>
                </div>
            </div>
        </div>
    @endif
