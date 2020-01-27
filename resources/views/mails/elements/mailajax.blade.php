<script>




    $('#nachrichten').on('click', '.mail-box-item', function (e) {

        var id = $(this).data('id');
        var date = $(this).data('date');
        var from = $(this).data('from');

        var arr = {id:id, date:date, from:from, _token: '{{csrf_token()}}'};

        var url = "{{url('getMail')}}" + "/" + id;
        var re = /_/gi;
        $('#trash').data('uid', id);
        $('#spam').data('uid', id);


        var item = this;

        $.ajax({
            dataType: "json",
            data: arr,
            type: "POST",
            url: url,
            beforeSend: function () {
                $('#wait').show();
                $('#loadModal').modal('show');

            },
            complete: function () {
                $('#wait').hide();
            },
            success: function (data) {
                $(item).removeClass('selected');
                $('#from').text(
                    "Von: " + data.Nachricht.from[0].full
                );
                $('#betreff').text(
                    data.Nachricht.subject.replace(re, " ")
                );
                if (data.Nachricht.bodies.html) {
                    $('#body').html(
                        data.Nachricht.bodies.html.content
                    );
                } else if (data.Nachricht.bodies.text) {
                    $('#body').html(
                        data.Nachricht.bodies.text.content.replace(/\n/g, '<br/>')
                    );
                } else {
                    $('#body').html(
                        "<p class='alert alert-warning'>Mailtext nicht gefunden</p>"
                    );
                }

                if ($(item).data('interessent').length > 9) {
                    $('#toInteressent').attr('href', $(item).data('interessent'));
                    $('#toInteressent').show();
                    $('#createInteressent').hide();

                } else {
                    $('#toInteressent').hide();
                    $('#createInteressent').show();
                    $('#email').val(data.Nachricht.from[0].mail);
                    $('#personal').val(data.Nachricht.from[0].personal);


                }

                $('#reply').attr("href", '{{url('reply')}}' + '/' + id);

                $('#loadModal').modal('hide');
                $('#MailModal').modal('show');
            },
            error: function (data) {
                $("#body").append('<li class="mail-box-item bg-danger text-white">Fehler beim Laden der E-Mails</li>');
            }
        });
    });
    $('#spam').on('click', function (e) {
        var uid = $(this).data('uid');
        var url = "{{url('spamMail')}}" + "/" + uid;

        $('#MailModal').modal('hide');
        $.ajax({
            dataType: "json",
            url: url,
            method: "POST",
            data: {'_token': "{{csrf_token()}}"},
            beforeSend: function () {
                $('#wait').show();
            },
            complete: function (data) {
                $('#wait').hide();

            },
            success: function (data) {

                $('*[data-id=' + uid + ']').remove();
            },
            error: function (data) {

            }
        });
    });
    $('#trash').on('click', function (e) {
        var uid = $(this).data('uid');
        var url = "{{url('deleteMail')}}" + "/" + uid;

        $('#MailModal').modal('hide');
        $.ajax({
            dataType: "json",
            url: url,
            method: "POST",
            data: {'_token': "{{csrf_token()}}"},
            beforeSend: function () {
                $('#wait').show();
            },
            complete: function () {
                $('#wait').hide();
            },
            success: function (data) {
                $('*[data-id=' + uid + ']').remove();
            },
            error: function (data) {
            }
        });
    });
    $('#createInteressent').on('click', function (e) {
        $('#newInteressent').submit();
    });

</script>