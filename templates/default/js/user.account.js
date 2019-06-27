/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var user_account = (function () {
    return {

        init: function () {

            $('#profileEdit').submit(function (e) {

                e.preventDefault();

                var f = $(this),
                    fio = $('[name="fio"]').val(),
                    email = $('[name="email"]').val(),
                    phone = $('[name="phone"]').val(),
                    messages = $('.messages');

                messages.hide().removeClass('messages--error messages--success').html('');

                $.ajax({
                    type: "POST",
                    url: "/ajax/user/",
                    dataType: "json",
                    data: {
                        action: 'updateProfile',
                        fio: fio,
                        email: email,
                        phone: phone
                    },
                    beforeSend: function(){
                        f.find('[type="submit"]').prop("disabled", true);
                    },
                    success: function(res){
                        if (res.status === 'success') { // message
                            messages.addClass('messages--success').html(res.message).show('slow');
                            setTimeout(function(){
                                messages.hide('slow').removeClass('messages--success').html('');
                            }, 10000);
                        } else {
                            messages.addClass('messages--error').html(res.message).show('slow');
                        }
                        f.find('[type="submit"]').prop("disabled", false);
                    }
                });
            });

            $('#profilePassEdit').validator().submit(function (e) {
                e.preventDefault();
                var f = $(this),
                    messages = $('.messages');

                messages.hide().removeClass('messages--error messages--success').html('');

                if (f.validator({}, 'check') > 0) {
                    return false;
                }

                var pass1 = $('[name="password_old"]').val(),
                    pass2 = $('[name="password_new"]').val(),
                    pass3 = $('[name="password_new_repeat"]').val();

                $.ajax({
                    type: "POST",
                    url: "/ajax/user/",
                    dataType: "json",
                    data: {
                        action: 'updateProfilePass',
                        pass1: pass1,
                        pass2: pass2,
                        pass3: pass3
                    },
                    beforeSend: function(){
                        f.find('[type="submit"]').prop("disabled", true);
                    },
                    success: function(res){
                        if (res.status === 'success') {
                            messages.addClass('messages--success').html(res.message).show('slow');
                            setTimeout(function(){
                                messages.hide('slow').removeClass('messages--success').html('');
                            }, 10000);
                            $('[name="password_new"]').val('');
                            $('[name="password_new_repeat"]').val('');
                        } else {
                            messages.addClass('messages--error').html(res.message).show('slow');
                        }

                        f.find('[type="submit"]').prop("disabled", false);
                    }
                });
                return false;
            });

            $("body").on('click', '.objectDeactivate', function () {
                var data = Pyshnov.actionObject($(this), 'deactivate');

                if(data.status === 'success') {
                    $(this).removeClass('objectDeactivate').addClass('objectActivate').text(data.message);
                    $(this).closest('.realty-list-content').find('.data_status').html('Снято с публикации');
                }
            }).on('click', '.objectActivate', function () {

                var data = Pyshnov.actionObject($(this), 'moderation');

                if(data.status === 'success') {
                    $(this).removeClass('objectActivate').addClass('objectDeactivate').text(data.message);
                    $(this).closest('.realty-list-content').find('.data_status').html('Ожидает модерации');
                }
            }).on('click', '.objectDelete', function () {
                var data = Pyshnov.actionObject($(this), 'delete');

                if (data.status === 'success') {
                    location.reload();
                }

            });

        }
    }
})();

$(document).ready(function () {
    user_account.init();
});