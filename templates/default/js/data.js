/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var data = (function () {
    return {
        init: function () {
            $('#formAdd').validator({}).on('submit', function (e) {
                if ($('#formAdd').validator({}, 'check') > 0) {
                    return false;
                } else {
                    if ($(".g-recaptcha").length && !grecaptcha.getResponse().length) {
                        grecaptcha.reset();
                        alert('Вы не прошли проверку "Я не робот".');
                        return false;
                    }
                }
            });
            $('#formEdit').validator({}).on('submit', function (e) {
                if ($(this).validator({}, 'check') > 0) {
                    return false;
                }
            });
            this.refreshTopic();
            $('#topicId').on('change', function () {
                data.refreshTopic();
            });
        },
        refreshTopic: function () {
            var select = $('#topicId'),
                topic_id = select.val(),
                id = [];
            select.closest('form').find('.refresh_topic').each(function () {
                id.push($(this));
            });
            id.forEach(function (el) {
                if (el.data('show').split(',').indexOf(topic_id) !== -1) {
                    el.show('slow');
                    if (el.attr('id') === 'squareRooms') {
                        $('.square_rooms').text((topic_id === '2' || topic_id === '3') ? 'Площадь комнаты' : 'Площадь комнат');
                    }
                } else {
                    el.hide('slow');
                }
            });
        }
    }
})();

$(document).ready(function () {
    data.init();
});