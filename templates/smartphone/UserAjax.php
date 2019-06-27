<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


class UserAjax extends \Pyshnov\Core\Ajax\AjaxResponse
{

    public function loadLocationHtml()
    {
        $html = <<<HTML

<div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Выбор города</h4>
            </div>
            <div class="modal-body">
                <ul class="location-list">
                    <div class="width-50pr">
                        <li><a class="js-city__link" data-id="1"><b>Санкт-Петербург</b></a></li>
                        <li><a class="js-city__link" data-id="2"><b>Москва</b></a></li>
                        <li><a class="js-city__link" data-id="6">Великие Луки</a></li>
                        <li><a class="js-city__link" data-id="328">Екатеринбург</a></li>
                        <li><a class="js-city__link" data-id="327">Калининград</a></li>
                        <li><a class="js-city__link" data-id="326">Нижний Новгород</a></li>
                    </div>
                    <div class="width-50pr">
                        <li><a class="js-city__link" data-id="325">Новосибирск</a></li>
                        <li><a class="js-city__link" data-id="324">Омск</a></li>
                        <li><a class="js-city__link" data-id="330">Оренбург</a></li>
                        <li><a class="js-city__link" data-id="5">Псков</a></li>
                        <li><a class="js-city__link" data-id="329">Самара</a></li>
                    </div>
                </ul>
            </div>
            <div class="modal-footer">
                <div class="col-xs-4">
                    <div class="row">
                        <div class="regions-select">
                            <select id="regionsLocation" class="geo-select" data-live-search="true" title="Выбрать регион" data-width="100%"></select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="row">
                        <div class="city-select">
                            <select id="cityLocation" class="geo-select" disabled title="Выберити город" data-width="100%"></select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="row">
                        <button id="applyRegion" class="btn btn-default" disabled >Выбрать</button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

HTML;
        $this->data['html'] = $html;

        return $this->render(true);
    }

    public function test()
    {
        $city_id = $this->getPostParam('city_id');

        $district = false;
        $metro = false;

        if ($city_id) {

            $form = new \Pyshnov\form\Form();

            $stmt = \Pyshnov\Core\DB\DB::select('district_id, name', DB_PREFIX . '_district')
                ->where('city_id', '=', $city_id)
                ->orderBy('name')
                ->execute()
                ->fetchAll();

            if (!empty($stmt)) {

                $district = '<a href="#" class="item-link smart-select" data-open-in="popup" data-popup-close-text="Назад">';
                $district .= '<select name="district_id" multiple>';

                foreach ($stmt as $item) {
                    $district .= '<option value="' . $item['district_id'] . '">' . $item['name'] . '</option>';
                }

                $district .= '</select>';
                $district .= '<div class="item-content">';
                $district .= '<div class="item-inner">';
                $district .= '<div class="item-title">Район</div>';
                $district .= '<div class="item-after">Не важно</div>';
                $district .= '</div>';
                $district .= '</div>';
                $district .= '</a>';
            }

            $stmt = \Pyshnov\Core\DB\DB::select('metro_id, name', DB_PREFIX . '_metro')
                ->where('city_id', '=', $city_id)
                ->orderBy('name')
                ->execute()
                ->fetchAll();

            if (!empty($stmt)) {

                $metro = '<a href="#" class="item-link smart-select" data-open-in="popup" data-popup-close-text="Назад">';
                $metro .= '<select name="metro_id" multiple>';

                foreach ($stmt as $item) {
                    $metro .= '<option value="' . $item['metro_id'] . '">' . $item['name'] . '</option>';
                }

                $metro .= '</select>';
                $metro .= '<div class="item-content">';
                $metro .= '<div class="item-inner">';
                $metro .= '<div class="item-title">Метро</div>';
                $metro .= '<div class="item-after">Не важно</div>';
                $metro .= '</div>';
                $metro .= '</div>';
                $metro .= '</a>';
            }

        }

        $this->data = [
            'city_id' => $city_id,
            'district' => $district,
            'metro' => $metro
        ];

        return $this->render(true);
    }

    public function loadCity() {
        $city_id = $this->getPostParam('city_id');
        $rows = \Pyshnov\Core\DB\DB::select('city_id, name', DB_PREFIX . '_city')
            ->where('active', '=', 1)
            ->orderBy('name')
            ->execute()
            ->fetchAll();

        $html = '';

        foreach ($rows as $item) {
            if ($city_id != $item['city_id']) {
                $html .= '<option value="' . $item['city_id'] . '">' . $item['name'] . '</option>';
            }
        }

        $this->data = $html;

        return $this->render(true);

    }

    public function getBaseLink() {
        $city_id = $this->getPostParam('city_id');

        $city = $this->get('city')->getCityById($city_id);

        $this->data = '/' . $city['aliases'] . '/arenda/';

        return $this->render(true);

    }

    public function heckEmail() {

        $res = false;

        $email = $this->getPostParam('email');
        $stmt = \Pyshnov\Core\DB\DB::select('email', DB_PREFIX . '_user')
            ->where('email', '=', $email)
            ->execute();

        if ($stmt->rowCount()) {
            $res = true;
        }

        return $this->render($res);

    }

}