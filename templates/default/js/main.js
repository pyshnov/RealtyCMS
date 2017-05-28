$(document).ready(function () {

    $('#ajaxFormLogin').validator({
        scrollTop: false
    }).on('submit', function (e) {
        e.preventDefault();
        signIn($(this));
    });


});

function signIn(form) {
    var modal = form.closest(".modal-content");
    var error_block = form.find('.ajax-error').hide();

    if (form.validator({
            scrollTop: false
        }, 'check') > 0) {
        modal.removeClass('flipInY');
        modal.addClass('shake');
        setTimeout(function () {
            modal.removeClass('shake');
        }, 1000);
    } else {
        $.ajax({
            type: "POST",
            url: "/ajax/signin/",
            dataType: "json",
            data: {
                s: form.serialize()
            },
            success: function (data) {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    if (data.data === 'blocked') {
                        form.parent(".modal-body").html(data.message);
                    } else {
                        error_block.text(data.message);
                        error_block.show();
                    }
                }
            }
        });
    }
}