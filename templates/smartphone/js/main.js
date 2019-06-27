/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

Pyshnov.adsToLoad = [];

console.log(Pyshnov.getRootUrl())

// Initialize app
var myApp = new Framework7({
    modalTitle: 'NET-AGENTA',
    pushState: true,
    pushStateSeparator: '#',
    //pushStateRoot: 'http://framework.loc/',
    //pushStateOnLoad: false,
    modalButtonCancel: "Отмена", //текст Cancel кнопки
    swipePanel: 'left',
    init: false,
    onAjaxStart: function (xhr) {
        myApp.showIndicator();
    },
    onAjaxComplete: function (xhr) {
        myApp.hideIndicator();
    }
});

// Add view
var mainView = myApp.addView('.view-main', {
    dynamicNavbar: true,
    //domCache: true //чтобы навигация работала без сбоев и с запоминанием scroll position в длинных списках
});

// If we need to use custom DOM library, let's save it to $$ variable:
var $$ = Dom7;

myApp.onPageInit('index', function (page) {

    loadReference();

    var load_city = false;

    $$('#smartLocation').on('click', function(){
        if (!load_city) {
            $$.ajax({
                async: false,
                type: "POST",
                url: "/ajax/",
                dataType: "json",
                data: {
                    action: 'loadCity',
                    city_id: $$('#cityId').val()
                },
                success: function (res) {
                    if (res.status === 'success') {
                        //$$('#cityId').html(res.data)
                        myApp.smartSelectAddOption('#cityId', res.data);
                        load_city = true;
                    }
                }
            });

        }
    });

    $(document).on('pageBeforeInit', '[data-select-name="city_id"]', function (e) {
        //var page = e.detail.page;
        $$('#cityId').on('change', function (e) {
            loadReference();
        });
    });

    $$('.form-to-data').on('click', function(){
        $$.ajax({
            async: false,
            type: "POST",
            url: "/ajax/",
            dataType: "json",
            data: {
                action: 'getBaseLink',
                city_id: $$('#cityId').val()
            },
            success: function (res) {
                if (res.status === 'success') {
                    $$('form.ajax-submit').attr('action', res.data)
                }
            }
        });

    });

    $$('form.ajax-submit').on('form:beforesend', function (e) {
        var city_id = $$('#cityId').val();
        var date = new Date(new Date().getTime() + 60 * 60 * 24 * 365);
        document.cookie = "cid=" + city_id + "; path=/; expires=" + date.toUTCString();
    });

    $$('form.ajax-submit').on('form:success', function (e) {
        var xhr = e.detail.xhr; // actual XHR object

        var data = e.detail.data; // Ajax response from action file
        // do something with response data
        //mainView.router.loadContent(data);
        // Перезагружаем страницу, чтобы появилась ссылка для фильтра
        mainView.router.loadPage(xhr.requestUrl);
    });
});

myApp.onPageInit('data-list', function (page) {

    var t = $$('.data-sort-by');

    t.on('click', function () {
        //mainView.router.reloadPage('/saint-petersburg/arenda/')
        var buttons = [
            {
                text: 'По дате создания',
                onClick: function () {
                    var link = t.data('link');
                    if (link !== undefined) {
                        mainView.router.reloadPage(link + 'date_added&asc=desc')
                    }
                }
            },
            {
                text: 'Увеличению цены',
                onClick: function () {
                    var link = t.data('link');
                    if (link !== undefined) {
                        mainView.router.reloadPage(link + 'price&asc=asc')
                    }
                }
            },
            {
                text: 'Уменьшению цены',
                onClick: function () {
                    var link = t.data('link');
                    if (link !== undefined) {
                        mainView.router.reloadPage(link + 'price&asc=desc')
                    }
                }
            },
            {
                text: 'Отмена'
            }
        ];
        myApp.actions(buttons);
    });

    $.getScript("//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js", function(){

        $$('.adsense').each( function() {
            if (Pyshnov.adsToLoad.indexOf(this) !== -1) {
                return;
            }

            Pyshnov.adsToLoad[Pyshnov.adsToLoad.length] = this;

            (adsbygoogle = window.adsbygoogle || []).push({});

        });
    });

    $(document).ready(function () {
        $(".page-content").on("scroll", scrolling);
    });

});

function scrolling() {
    var currentHeight = $(this).children(".realty-list").height();

    if ($(this).scrollTop() >= (currentHeight - $(this).height() - 100)) {

        $(this).unbind("scroll");

        loaderList();
    }
}

function loaderList() {

    var next_page = $$('.realty-list', mainView.activePage.container).data('next_page');

    if (next_page === undefined || next_page.length === 0) {
        return;
    }

    var list = $$('.realty-list ul');

    $$.ajax({
        async: false,
        type: "GET",
        url: next_page,
        beforeSend: function(){
            list.append('<li class="ajax-content-load"><span class="preloader preloader-green"></span></li>');
        },
        success: function(data){
            var el = document.createElement('div');
            el.innerHTML = data;

            next_page = $$(el).find('.realty-list').data('next_page');

            $$('.realty-list').data('next_page', (next_page || ''));

            $$('.ajax-content-load').remove();

            list.append($(el).find( '.realty-list ul' ).html());

            $$('.adsense').each( function() {

                if (Pyshnov.adsToLoad.indexOf(this) !== -1) {
                    return;
                }

                Pyshnov.adsToLoad[Pyshnov.adsToLoad.length] = this;

                (adsbygoogle = window.adsbygoogle || []).push({});

            });

            $(".page-content").on("scroll", scrolling);
        }
    });
}

myApp.onPageInit('data-view', function (page) {
    $.getScript("/templates/smartphone/fotorama/fotorama.js");
    $('.fotorama').on('fotorama:show', function (e, fotorama, extra) {
        $('.fotorama__photo-counter').html(fotorama.activeIndex + 1 + ' из ' + fotorama.size)
    });

    $('#openPhone').on('click', function (e) {
        e.preventDefault();
        Pyshnov.openPhone('.phone', '#openPhone', $(this).data('id'))
    });

    $('#complain').on('click', 'a', function(e){
        e.preventDefault();

        var id = Number($(this).data('id')),
            text = $(this).text(),
            res_ok = $(".complain-ok");

        if (id) {
            $.ajax({
                type: "POST",
                url: "/ajax/data/",
                dataType: "json",
                data: {
                    action: "addComplaint",
                    id: id,
                    text: text
                },
                beforeSend: function(){
                    res_ok.html('<i class="fa fa-spinner fa-spin"></i>');
                },
                success: function(data){
                    if(data.status === 'success'){

                        $$('.complain-btn').remove();
                        myApp.closeModal('.popover-complain', true);

                        res_ok.html(data.message).show('slow');
                    }
                }
            });
        }
    });

    $.getScript("https://api-maps.yandex.ru/2.1/?lang=ru_RU", function(){

        ymaps.ready( function () {

            var geo = $$('#map').data('geo');

            if (geo === undefined || geo.length === 0) {
                return;
            }

            var coordinate = geo.split(","),
                myMap;

            $$('.static-map').on('click', function () {
                myApp.popup('.popup-map');
            });

            $$('.popup-map').on('popup:open', function () {
                myMap = new ymaps.Map('map', {
                    center: coordinate,
                    zoom: 16,
                    controls: ['zoomControl', 'geolocationControl', 'rulerControl']
                });
                myMap.geoObjects.add(new ymaps.Placemark(coordinate, {}, {
                    preset: 'islands#dotIcon',
                    iconColor: '#18a689'
                }));

                var button = new ymaps.control.Button({
                    options: {
                        layout: ymaps.templateLayoutFactory.createClass(
                            '<button class="button map-close close-popup"></button>'
                        )
                    }});
                myMap.controls.add(button, {float: 'right'});
            }).on('popup:close', function () {
                myMap.destroy();// Деструктор карты
                myMap = null;
            });
        });
    });

    /** фото **
    var photos = [];

    $$('.fotorama').find('a').each(function(indx) {
        photos[indx] = $$(this).data('full');
    });

    var photoBrowser = myApp.photoBrowser({

        photos : photos,
        lazyLoading: 'true',
        type: 'popup',
        toolbarTemplate: ' '
    });
    $$('.pb-popup').on('click', function () {
        photoBrowser.open();
    });*/

});

myApp.onPageInit('user-register', function (page) {

    $$('.form-to-data').on('click', function(){

        var form = $$('#registerForm'),
            email = form.find('[name=email]').val(),
            pass = form.find('[name=password]').val(),
            pass2 = form.find('[name=password2]').val(),
            error = [];

        if (email.length === 0) {
            error.push('Поле "Email" не может быть пустым');
        } else if (/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/.test(email) === false) {
            error.push('Не верный формат Email');
        }

        if (pass.length === 0 || pass2.length === 0) {
            error.push('Поля "Пароль" не могут быть пустыми');
        }

        if(pass !== pass2) {
            error.push('Пароли не совпадают');
        }

        $$.ajax({
            async: false,
            type: "POST",
            url: "/ajax/",
            dataType: "json",
            data: {
                action: "heckEmail",
                email: email
            },
            success: function(data){
                if(data.status === 'success'){
                    error.push('Такой email уже используется');
                }
            }
        });

        if (error.length > 0) {
            myApp.addNotification({
                title: 'Ошибка',
                message: error.join('<br>')
            });

            setTimeout(function() {
                myApp.closeNotification('.notifications')
            }, 5000);

            return false;
        }

        $$('form.ajax-submit').on('form:success', function (e) {
            window.location.href = '/';
        });
    });

});

myApp.init();


$$('.panel-left').on('click', '.item-link', function () {
    myApp.closePanel()
});

var loginForm = $$('.ajax-form-login');
loginForm.find('.list-button').on('click', function () {
    var email = loginForm.find('input[name="email"]').val(),
        pass = loginForm.find('input[name="pass"]').val(),
        str = 'email=' + email + '&pass=' + pass + '&rememberme=1';

    if (email && pass) {
        $$.ajax({
            type: "POST",
            url: "/ajax/signin/",
            dataType: "json",
            data: {
                s: str
            },
            success: function (data) {
                if (data.status === 'success') {
                    //myApp.closeModal('.login-screen');
                    //mainView.router.reloadPage(mainView.url);

                    if (window.location.hash.length > 1) {
                        window.location.href = window.location.hash.slice(1);
                    } else {
                        window.location.reload(true);
                    }
                } else {
                    if (data.data === 'blocked') {
                        loginForm.html('<div class="content-block">' + data.message + '</div>');
                    } else {
                        myApp.alert(data.message);
                    }
                }
            }
        });
    }
});


function loadReference() {

    var city_id = $$('#cityId').val();

    if (!city_id) {
        return;
    }

    $$.ajax({
        type: "POST",
        url: "/ajax/",
        dataType: "json",
        cache: false,
        data: {
            action: 'test',
            city_id: city_id
        },
        success: function (res) {
            if (res.status === 'success') {

                var district = $$('#district');
                var metro = $$('#metro');

                if (res.data.district) {
                    district.html(res.data.district);
                    district.show();
                } else {
                    district.hide().html('');
                }

                if (res.data.metro) {
                    metro.html(res.data.metro);
                    metro.show();
                } else {
                    metro.hide().html('');
                }
            }

        }
    });
}


