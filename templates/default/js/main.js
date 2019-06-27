$(document).ready(function () {

    $('#ajaxFormLogin').validator({
        scrollTop: false
    }).on('submit', function (e) {
        e.preventDefault();
        signIn($(this));
    });

    $('#openPhone').on('click', function (e) {
        e.preventDefault();
        Pyshnov.openPhone('.phone', '#openPhone', $(this).data('id'))
    });

    // Регистрация нового пользователя
    $("#registerForm").validator().on('submit', function (e) {
        var  t = $(this);
        if (t.validator({}, 'check') > 0) {
            return false;
        } else {
            var pass = t.find('[name=password]');
            var pass2 = t.find('[name=password2]');
            if(pass.val() !== pass2.val()) {

                var arr = [
                    pass.closest('.form-group'),
                    pass2.closest('.form-group')
                ],
                    message = '<div class="help-block-errors">Пароли не совпадают</div>';

                arr.forEach(function(item) {
                    item.find('.help-block-errors').remove();
                    item.append(message);
                    item.addClass('has-error');
                });

                return false;
            }
        }
    });

    $('.form_validator').validator().on('submit', function (e) {
        if ($(this).validator({}, 'check') > 0) {
            return false;
        }
    });

    $('.header_geoselector').on('click', function (e) {
        locationHtml(e)
    });

    //------ Плайщий рекламный блок ---------//
    (function () {
        var a = document.querySelector('#yandex_ads'),
            b = null,
            P = 15;  // если ноль заменить на число, то блок будет прилипать до того, как верхний край окна браузера дойдёт до верхнего края элемента. Может быть отрицательным числом

        if (a === null) return;

        window.addEventListener('scroll', Ascroll, false);
        document.body.addEventListener('scroll', Ascroll, false);
        function Ascroll() {
            if (b === null) {
                var Sa = getComputedStyle(a, ''),
                    s = '';
                for (var i = 0; i < Sa.length; i++) {
                    if (Sa[i].indexOf('overflow') == 0
                        || Sa[i].indexOf('padding') == 0
                        || Sa[i].indexOf('border') == 0
                        || Sa[i].indexOf('outline') == 0
                        || Sa[i].indexOf('box-shadow') == 0
                        || Sa[i].indexOf('background') == 0) {
                        s += Sa[i] + ': ' + Sa.getPropertyValue(Sa[i]) + '; '
                    }
                }
                b = document.createElement('div');
                b.style.cssText = s + ' box-sizing: border-box; width: ' + a.offsetWidth + 'px;';
                a.insertBefore(b, a.firstChild);
                var l = a.childNodes.length;
                for (var i = 1; i < l; i++) {
                    b.appendChild(a.childNodes[1]);
                }
                a.style.height = b.getBoundingClientRect().height + 'px';
                a.style.padding = '0';
                a.style.border = '0';
            }
            var Ra = a.getBoundingClientRect(),
                R = Math.round(Ra.top + b.getBoundingClientRect().height - document.querySelector('.footer').getBoundingClientRect().top + 0);  // селектор блока, при достижении верхнего края которого нужно открепить прилипающий элемент;  Math.round() только для IE; если ноль заменить на число, то блок будет прилипать до того, как нижний край элемента дойдёт до футера
            if ((Ra.top - P) <= 0) {
                if ((Ra.top - P) <= R) {
                    b.className = 'stop';
                    b.style.top = -R + 'px';
                } else {
                    b.className = 'sticky';
                    b.style.top = P + 'px';
                }
            } else {
                b.className = '';
                b.style.top = '';
            }
            window.addEventListener('resize', function () {
                a.children[0].style.width = getComputedStyle(a, '').width
            }, false);
        }
    })();

    // Как только модальное окно открылось
    // добалим скрол, если содержимое слишком большое
    $('.modal.modal-filter').on('show.bs.modal', function (e) {
        var t = $(this);
        mh(t);
        $(window).resize(function () {
            mh(t);
        });
    });

    // Если клик по элементу (район, метро...)
    $('.modal-filter').on('click', 'input', function () {
        modalFilter($(this).attr('name'), $(this).val());
    });

    $('#sideSearch').submit(function () {
        var f = $(this);
        ['district', 'metro'].forEach(function (item) {
            var e = window[item + '_id'];
            if (e !== undefined && e.length) {
                f.append('<input type="hidden" name="' + item + '_id" value="' + e.join(',') + '">');
            }
        });
    });

    // Если ранее был применен фильт по районам, метро....
    // после перезагрузки страницы отобразим количество на кнопках
    ['district', 'metro'].forEach(function (item) {
        if (window[item + '_id'] !== undefined) {
            var count = window[item + '_id'].length;
            if (count) {
                $('.' + item + '-btn span').html(' (' + count + ')<a href="javascript:void(0);" onclick="clearModalFilter(\'' + item + '\'); return false;">×</a>');
            }
        }
    });

    Pyshnov.formatNumber('.price-mask');

    $('.btn-popover').popover({trigger: 'hover'});

});

function mh(t) {
    var e = $(".modal-body", t);
    e.length && e.css({
        maxHeight: $(window).height() - 198,
        overflow: "auto"
    })
}

function modalFilter(name, value) {
    var t = window,
        name_id = name + '_id',
        val = +value,
        idx = t[name_id].indexOf(val);

    if (idx !== -1) {
        t[name_id].splice(idx, 1)
    } else {
        t[name_id].push(val);
    }

    var count = t[name_id].length;

    if (count) {
        $('.' + name + '-btn span').html(' (' + count + ')<a href="javascript:void(0);" onclick="clearModalFilter(\'' + name + '\'); return false;">×</a>');
    } else {
        $('.' + name + '-btn span').text('');
    }
}

function clearModalFilter(e) {
    window[e + '_id'] = [];
    $('.' + e + '-btn span').text('');
    $('#' + e + 'Modal input[type="checkbox"]').prop("checked", false);
}

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

var locationHtml = function () {
    var t = this;
    if ('undefined' == typeof geoLocation) {
        $.ajax({
            async: false,
            type: "POST",
            url: "/ajax/",
            dataType: "json",
            data: {
                action: 'loadLocationHtml'
            },
            success: function (res) {
                if (res.status === 'success') {
                    $(".footer").after(res.data.html);
                    $('.geo-select').selectpicker('refresh');
                }
            }
        });

        this.geoLocation = function () {
            var apply = $("#applyRegion"),
                region_loc = $('#regionsLocation'),
                location = $("#cityLocation");

            region_loc.on('show.bs.select', function () {

                // Если впервые, ajax получаем список регионов
                if (t.isRegionLocation === undefined) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/data/",
                        dataType: "json",
                        data: {
                            action: 'loadRegionsHtml'
                        },
                        beforeSend: function () {
                            region_loc.html('<i class="fa fa-spinner fa-spin"></i>').selectpicker('refresh');
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                region_loc.html(res.data).selectpicker('refresh');
                                t.isRegionLocation = true;
                            }
                        }
                    });
                }
            }).on('change', function () {
                $.ajax({
                    type: "POST",
                    url: "/ajax/data/",
                    dataType: "json",
                    data: {
                        action: 'loadCityHtml',
                        id: region_loc.val()
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            location.html(res.data);
                            if ($("option", location).size() > 1) {
                                location.prop("disabled", false).on('change', function () {
                                    apply.prop("disabled", false);
                                });
                                apply.prop("disabled", true);
                            } else {
                                location.prop("disabled", true);
                                apply.prop("disabled", false);
                            }
                        } else {
                            apply.prop("disabled", true);
                            location.prop("disabled", true);
                        }
                        location.selectpicker('refresh');
                    }
                });
            });
            $('.js-city__link').on('click', function () {
                var id = $(this).data('id');
                $.cookie('cid', id, {expires: 365, path: '/'});
                t.location.href = '/';
            });

            apply.on('click', function () {
                var id = location.val();
                $.cookie('cid', id, {expires: 365, path: '/'});
                t.location.href = '/';
            });
        }
    }

    $('#locationModal').modal('show');
    t.geoLocation();
};

/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD (Register as an anonymous module)
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {

    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape...
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            // Replace server-side written pluses with spaces.
            // If we can't decode the cookie, ignore it, it's unusable.
            // If we can't parse the cookie, ignore it, it's unusable.
            s = decodeURIComponent(s.replace(pluses, ' '));
            return config.json ? JSON.parse(s) : s;
        } catch (e) {
        }
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function (key, value, options) {

        // Write

        if (arguments.length > 1 && !$.isFunction(value)) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // Read

        var result = key ? undefined : {},
            // To prevent the for loop in the first place assign an empty array
            // in case there are no cookies at all. Also prevents odd result when
            // calling $.cookie().
            cookies = document.cookie ? document.cookie.split('; ') : [],
            i = 0,
            l = cookies.length;

        for (; i < l; i++) {
            var parts = cookies[i].split('='),
                name = decode(parts.shift()),
                cookie = parts.join('=');

            if (key === name) {
                // If second argument (value) is a function it's a converter...
                result = read(cookie, value);
                break;
            }

            // Prevent storing a cookie that we couldn't decode.
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
        // Must not alter options, thus extending a fresh object...
        $.cookie(key, '', $.extend({}, options, {expires: -1}));
        return !$.cookie(key);
    };

}));
