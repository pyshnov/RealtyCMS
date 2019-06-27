/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

ymaps.ready(function () {
    function trim(s) {
        return s.trim().replace(new RegExp('\\s+', 'g'), ' ');
    }

    function geoObject(r) {
        var object = r.geoObjects.get(0);
        object.info = object.properties.get('metaDataProperty.GeocoderMetaData');
        object.status = object.info.kind == 'house';

        return object;
    }

    var message = '<span class="message">Укажите точный адресс</span>',
        district = $('#formDistrict'),
        metro = $('#formMetro'),
        time_metro = $('#timeMetro'),
        city_id = $('#cityId'),
        city_name = null;

    function actionClass(suggest, object) {
        suggest.form_group.removeClass('has-error');
        $('.help-block-errors', suggest.form_group).remove();
        if (!object.status) {
            suggest.form_group.addClass('has-error');
            suggest.input.after('<div class="help-block-errors">' + message + '</div>');
        } else {

            var city = /<p>([^,]+),\s/i.exec(object.properties.get('balloonContent'));

            if (city !== null && trim(city[1]) !== city_name) {
                city_name = trim(city[1]);
                $.ajax({
                    type: "POST",
                    url: "/ajax/",
                    dataType: "json",
                    data: {
                        action: 'test',
                        city: city_name
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            if (res.data.district) {
                                district.find('.select').html(res.data.district).find('select').selectpicker('refresh');
                                district.show("slow");
                            } else {
                                district.hide("slow").find('.select').html('');
                            }

                            if (res.data.metro) {
                                metro.find('.select').html(res.data.metro).find('select').selectpicker('refresh');
                                metro.show("slow");
                                time_metro.show("slow");
                            } else {
                                metro.hide("slow").find('.select').html('');
                                time_metro.hide("slow");
                            }

                            city_id.val(res.data.city_id);
                        }
                    }
                });
            }
        }
    }

    map = new ymaps.Map('mapAdd', {
            center: [59.939095, 30.315868],
            zoom: 12,
            controls: ['zoomControl']
        }, {
            suppressMapOpenBlock: true // Ссылка «Открыть в Яндекс.Картах»
        }
    );
    map.behaviors.disable('scrollZoom');
    myGeoObject = new ymaps.GeoObject({
        geometry: {
            type: "Point",
            coordinates: []
        },
        properties: {
            iconContent: ''
        }
    }, {
        preset: 'islands#greenStretchyIcon',
        draggable: true
    });
    suggest = new ymaps.SuggestView('address', {offset: [0, 3], results: 10});
    suggest.input = $('#address');
    suggest.form_group = suggest.input.closest('.form-group');
    suggest.events.add('select', function (query) {
        suggest.selected = trim(query.get('item').value);
        ymaps.geocode(suggest.selected, {results: 1}).then(function (res) {
            var object = geoObject(res);
            myGeoObject.geometry.setCoordinates(object.geometry.getCoordinates());
            myGeoObject.properties.set('iconContent', object.status ? '' : message);
            map.setBounds(object.properties.get('boundedBy'), {checkZoomRange: true}); // центр
            map.geoObjects.add(myGeoObject);
            actionClass(suggest, object);
            myGeoObject.events.add('dragend', function (event) {
                ymaps.geocode(event.get('target').geometry.getCoordinates(), {results: 1}).then(function (res) {
                    var object = geoObject(res);
                    myGeoObject.properties.set('iconContent', object.status ? '' : message);
                    suggest.state.set('request', res.geoObjects.get(0).properties.get('metaDataProperty.GeocoderMetaData').text);
                    actionClass(suggest, object);
                });
            });
        });
    });
    window.map = map;
});
