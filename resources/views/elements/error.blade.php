@if($errors && $errors->count() != 0)
    <div class="container">
        <div class="row">
            <div class="col-md-10" >
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Warung! Es ist ein Fehler aufgetreten.</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
    </div>
@endif
