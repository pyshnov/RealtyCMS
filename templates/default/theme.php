<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * Функия препроцессора для всей программы
 * @param $variables
 */
function default_pre_process(&$variables)
{

    $city = Pyshnov::city();

    $variables['dd_location'] = defaultLocation($city);

    $query = \Pyshnov\Core\DB\DB::select('id', DB_PREFIX . '_data')
        ->where('city_id', '=', $city->getId())
        ->where('active', '=', 1)
        ->execute();
    $variables['count_object_city'] = $query->rowCount();
    $variables['city_name'] = $city->getName();
    $variables['city_id'] = $city->getId();

}

/**
 * @param \Pyshnov\location\City $city
 *
 * @return string
 */
function defaultLocation($city)
{
    $location_popular = [
        'saint-petersburg' => 'Санкт-Петербург',
        'moskva' => 'Москва',
        'nizhniy_novgorod' => 'Нижний Новгород',
        'pskov' => 'Псков',
        'samara' => 'Самара'
    ];

    $html = '<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $html .= mb_strtoupper($city->getName(), 'utf-8');
    $html .= '<i class="caret animated"></i></a>';
    $html .= '<ul class="dropdown-menu dropdown-menu-left animated fadeIn">';
    $html .= '<li class="drop-first"></li>';
    foreach ($location_popular as $aliases => $name) {
        if($city->getAlias() == $aliases) continue;
        $html .= '<li><a href="/' . $aliases . '/">' . $name . '</a></li>';
    }
    $html .= '<li role="separator" class="divider"></li>';
    $html .= '<li><a class="location_js" href="#">Выбрать другой</a></li>';
    $html .= '</ul>';

    return $html;
}

/**
 * Функция препроцессора файла шаблона главной страницы
 * @param $variables
 */
function default_pre_process_main(&$variables)
{

    $variables['range_price'] = getRangePrice();

}

/**
 * Вернет диапозон цен для определнной категории
 *
 * @param $topic_id
 * @return string
 */
function getRangePrice(){

    $res = [];

    foreach (Pyshnov::service('category')->getChildren()[1] as $id) {
        $stmt = \Pyshnov\Core\DB\DB::select('MIN(price) AS min_price, MAX(price) AS max_price', DB_PREFIX . '_data')
            ->where('active', '=', 1)
            ->where('lease_period', '=', 1)
            ->where('topic_id', '=', $id)
            ->where('city_id', '=', Pyshnov::city()->getId())
            ->execute();

        $row = $stmt->fetchAll();

        $min_price = $row[0]['min_price'];
        $max_price = $row[0]['max_price'];

        $res[$id] = 'от ' . ($min_price ?? '0' ) . ' до ' . ($max_price ?? '--');
    }

    return $res;
}

