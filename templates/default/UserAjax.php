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
        $city = $this->getPostParam('city');
        $city_loc = $this->get('location')->getCity();

        if ($city != $city_loc->getName()) {
            $query = \Pyshnov\Core\DB\DB::select('city_id', DB_PREFIX . '_city')
                ->where('name', '=', $city)
                ->execute()
                ->fetch();

            if ($query) {
                $city_id = $query['city_id'];
            } else {
                $city_id = 0;
            }
        } else {
            $city_id = $city_loc->getId();
        }

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
                $option = [];
                foreach ($stmt as $item) {
                    $option[$item['district_id']] = $item['name'];
                }

                $district = $form->addSelect('district', null, false, $option)
                    ->addAttribute('title', Pyshnov::t('system.district_zero_select'))
                    ->setClass('form-control')
                    ->render();
            }

            $stmt = \Pyshnov\Core\DB\DB::select('metro_id, name', DB_PREFIX . '_metro')
                ->where('city_id', '=', $city_id)
                ->orderBy('name')
                ->execute()
                ->fetchAll();

            if (!empty($stmt)) {
                $option = [];
                foreach ($stmt as $item) {
                    $option[$item['metro_id']] = $item['name'];
                }

                $metro = $form->addSelect('metro', null, false, $option)
                    ->addAttribute('title', Pyshnov::t('system.metro_zero_select'))
                    ->setClass('form-control')
                    ->render();
            }

        }

        $this->data = [
            'city_id' => $city_id,
            'district' => $district,
            'metro' => $metro
        ];

        return $this->render(true);
    }

}