<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function default_pre_process_user_signin(&$variables) {
    $variables['return_url'] = Pyshnov::request()->server->get('HTTP_REFERER', '/account/profile/');
}

/**
 * Функия препроцессора для всей программы
 * @param $variables
 */
function default_pre_process(&$variables)
{

    $city = Pyshnov::city();

    $query = \Pyshnov\Core\DB\DB::select('id', DB_PREFIX . '_data')
        ->where('city_id', '=', $city->getId())
        ->where('active', '=', 1)
        ->execute();
    $variables['count_object_city'] = $query->rowCount();
    $variables['city_name'] = $city->getName();
    $variables['city_id'] = $city->getId();

    if (Pyshnov::request()->attributes->has('_location')) {
        default_filter_pre_process($variables);
    }
}

/**
 * Функция препроцессора файла шаблона карточки объекта
 * @param $variables
 */
function default_pre_process_data_realty_view(&$variables)
{
    default_filter_pre_process($variables);
}

/**
 * добавление нового объявления
 *
 * @param $variables
 */
function default_pre_process_data_add(&$variables)
{
    $variables['category']->setOptionDisabled(1)->setClass('form-control');
}

function default_pre_process_user_data_edit(&$variables) {
    $variables['category']->setOptionDisabled(1)->setClass('form-control');

    if ($variables['district']) {
        $variables['district']->setClass('form-control');
    }

    if ($variables['metro']) {
        $variables['metro']->setClass('form-control');
    }
}

/**
 * Функция препроцессора файла шаблона главной страницы
 * @param $variables
 */
function default_pre_process_main(&$variables)
{

}

/**
 * Функция подготовит необходимые данные для фильтра объетов
 * Вынесенно в отдельную функция для того, что бы не дублировать данные
 * так как фильтр может использоваться в разных шаблонах
 * @param $variables
 */
function default_filter_pre_process(&$variables) {
    $query = Pyshnov::request()->query;

    if($variables['district']) {

        $districts = $variables['district'];

        $variables['district_list'] = getDistrictList($districts, $query);
        $variables['query_district_id'] = $query->get('district_id') ?? '';

        $variables['districts_count'] = count($districts->getOptions());
    }

    if($variables['metro']) {
        $metro = $variables['metro'];
        $variables['metro_list'] = getMetroList($metro, $query);
        $variables['query_metro_id'] = $query->get('metro_id') ?? '';

        $variables['metro_count'] = count($metro->getOptions());
    }

    $variables['filter_get_params'] = [
        'has_photo' => $query->get('has_photo', false),
        'posted_days' => (int)$query->get('posted_days', 0),
        'time_lease' => $query->get('time_lease', 1)
    ];
    $variables['category']->setOptionDisabled(1);
}

/**
 * @param \Pyshnov\form\Element\Select $districts
 * @param $query
 *
 * @return string
 */
function getDistrictList($districts, $query) {

    $districts = $districts->getOptions();

    # "Память"
    $memory = null;

    # Новый массив
    $sorting = [];

    $total = ceil(count($districts) / 3);

    # Обходим массив
    foreach($districts as $id => $name)
    {
        # Получаем первую букву
        $letter = mb_substr($name, 0, 1, 'utf-8');

        # Если текущая буква не равна предыдущей
        if( $letter != $memory )
        {
            # Заносим букву в "память"
            $memory = $letter;

            # Добавляем новый массив
            $sorting[$memory] = [];
        }

        # Дополняем массив
        $sorting[$memory][$id] = $name;
    }

    $html = '';

    $district_query = explode(',', $query->get('district_id'));

    $i = 0;

    foreach ($sorting as $key => $value) {
        $html .= '<div class="filter_modal_title">' . $key . '</div>';
        $cv = count($value);
        $c = 0;
        foreach ($value as $id => $name) {

            $i++;

            $html .= '<div class="checkbox small">';
            $html .= '<input id="d-' . $id . '" type="checkbox" name="district" 
                value="' . $id . '"' . (in_array($id, $district_query) ? ' checked' : '') . '>
                <label for="d-' . $id . '">' . $name . '</label>';
            $html .= '</div>';
            $c++;
            if($i == $total) {
                $html .= '</div><div class="col-xs-12 col-sm-8">';
                if($cv != $c) {
                    $html .= '<div class="filter_modal_title">' . $key . '</div>';
                }

                $i = 0;
            }
        }
    }

    return $html;
}

function getMetroList($metro, $query) {

    $metro = $metro->getOptions();

    # "Память"
    $memory = null;

    # Новый массив
    $sorting = [];

    $total = ceil(count($metro) / 3);

    # Обходим массив
    foreach($metro as $id => $name)
    {
        # Получаем первую букву
        $letter = mb_substr($name, 0, 1, 'utf-8');

        # Если текущая буква не равна предыдущей
        if( $letter != $memory )
        {
            # Заносим букву в "память"
            $memory = $letter;

            # Добавляем новый массив
            $sorting[$memory] = [];
        }

        # Дополняем массив
        $sorting[$memory][$id] = $name;
    }

    $html = '';

    $metro_query = explode(',', $query->get('metro_id'));

    $i = 0;

    foreach ($sorting as $key => $value) {
        $html .= '<div class="filter_modal_title">' . $key . '</div>';

        $cv = count($value);
        $c = 0;
        foreach ($value as $id => $name) {

            $i++;

            $html .= '<div class="checkbox small">';
            $html .= '<input id="m-' . $id . '" name="metro" type="checkbox" 
                value="' . $id . '"' . (in_array($id, $metro_query) ? ' checked' : '') . '>
                <label for="m-' . $id . '">' . $name . '</label>';
            $html .= '</div>';
            $c++;
            if($i == $total) {
                $html .= '</div><div class="col-xs-12 col-sm-8">';
                if($cv != $c) {
                    $html .= '<div class="filter_modal_title">' . $key . '</div>';
                }

                $i = 0;
            }
        }
    }

    return $html;
}
