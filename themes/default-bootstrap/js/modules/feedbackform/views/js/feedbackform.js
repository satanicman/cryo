$(document).ready(function() {
    $("#feedbackform--btn").fancybox({
        'href'   : '#feedbackform',
        'titleShow'  : false,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
        'padding' : 0,
        'margin' : 0,
        'closeBtn' : false,
        'helpers' : {
            // 'overlay': null
        },
        'maxWidth' : 750
    });
    $(document).on('submit', '#feedbackform', function(event) {
        var form = $(this).serialize(),
            formControl = $(this).find('.form-control');
        event.preventDefault();
        formControl.removeClass('empty');
        formControl.each(function(index, el) {
            if($(this).hasClass('is_required') && $(this).val().trim() === '')
                $(this).addClass('empty');
        });

        if($(this).find('.empty').length)
            return false;

        $.ajax({
            url: baseDir + 'modules/feedbackform/feedbackform-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: form,
            success: function(data) {
                var message = $('#feedbackform .message');
                message.slideDown('fast');
                if(data.hasError)
                    message.html(data.error);
                else {
                    message.html(data.message);
                    setTimeout(function() {
                        $.fancybox.close();
                        message.hide('fast').html('');
                        $('#feedbackform .form-control').val('');
                    }, 3000);
                }
            }
        });
    });

});