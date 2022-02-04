<div id="MailModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" >
                    <span>
                        <a class="btn btn-primary-outline" id="toInteressent" href="">
                            <i class="font-icon font-icon-user color-blue-grey-lighter"></i>
                        </a>

                        <button form="newInteressent" type="submit" class="btn btn-primary-outline" id="createInteressent" href="">
                            <i class="fa fa-user-plus color-blue-grey-lighter"></i>
                        </button>
                        <a class="btn btn-primary-outline" href="" id="reply">
                            <i class="fa fa-mail-reply"></i>
                        </a>
                        <a class="btn btn-primary-outline" id="trash" data-uid="">
                            <i class="fa fa-trash-o"></i>
                        </a>
                        <button id="spam" class="btn btn-danger btn-sm btn-rounded"  data-uid=""> <i class="fa fa-exclamation-triangle"></i>  </button>
                    </span>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <span id="from"></span>
                <h4 id="betreff">

                </h4>
                <p id="body">
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="loadModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" >
                    Nachricht wird geladen
            </div>
            <div class="modal-body">
                <img src="{{asset('img/ajax-loader.gif')}}" id="wait" width="100px">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<form id="newInteressent" action="{{url('newInteressent')}}" method="post">
    @csrf
    @method('POST')
    <input name="email" type="hidden" id="email">
    <input name="personal" type="hidden" id="personal">
</form>