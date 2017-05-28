<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Geocode\Geocode;
use Pyshnov\Core\Helpers\Price;
use Pyshnov\data\Filter;
use Pyshnov\data\Form\Upload;
use Pyshnov\system\Model\BaseModel;
use Pyshnov\system\Plugin\Pagination\Pagination;

class DataModel extends BaseModel
{

    use Filter;

    /**
     * Вернет количество активных объектов
     *
     * @return int
     */
    public function countActive()
    {
        return DB::select('id', DB_PREFIX . '_data')
            ->where('active', '=', 1)
            ->execute()
            ->rowCount();
    }

    /**
     * Вернет количество не активных объектов
     *
     * @return int
     */
    public function countUnActive()
    {
        return DB::select('id', DB_PREFIX . '_data')
            ->where('active', '=', 0)
            ->execute()
            ->rowCount();
    }

    /**
     * Вернет количество объектов на модерации
     *
     * @return int
     */
    public function countModeration()
    {
        return DB::select('id', DB_PREFIX . '_data')
            ->where('status_data', '=', 2)
            ->execute()
            ->rowCount();
    }

    public function getDataAll()
    {
        $params = $this->request()->query->all();

        $page = isset($params['page']) ? $params['page'] : 1;

        $params_sort = $this->prepareFilterParameters($params);

        $stmt_count = DB::select('id', DB_PREFIX . '_data')
            ->where($params_sort['columns_count'])
            ->setValues($params_sort['value'])
            ->execute()->rowCount();

        $page_limit_params = $this->prepareLimitParams($page, (int)$stmt_count, $this->config()->get('per_page_admin'));

        $order = $this->prepareSortOrder($params);

        $start = $page_limit_params['start'];
        $limit = $page_limit_params['limit'];

        // Получем постраничную навигацию
        $pagination = new Pagination($stmt_count, $limit, $page);
        $pagination->setLink($this->request()->getPathInfo())
            ->setQueryParams($params)
            ->setMaxItem(6);

        $stmt = DB::select('d.*, t.name AS type_sh, c.name AS city_name, s.name AS street_name, u.login, u.email',
            DB_PREFIX . '_data d')
            ->leftJoin(DB_PREFIX . '_topic t', 'd.topic_id', '=', 't.id')
            ->leftJoin(DB_PREFIX . '_city c', 'd.city_id', '=', 'c.city_id')
            ->leftJoin(DB_PREFIX . '_street s', 'd.street_id', '=', 's.street_id')
            ->leftJoin(DB_PREFIX . '_user u', 'd.user_id', '=', 'u.user_id')
            ->where($params_sort['columns'])
            ->setValues($params_sort['value'])
            ->orderBy($order)->limit($limit, $start)->execute();

        $result = new \stdClass();

        $result->pager = $pagination;
        $result->count = $stmt_count;
        $result->rows = $stmt->fetchAll();

        return $result;
    }

    public function prepareLimitParams($page, $total, $limit)
    {
        // Количество сраниц
        $max_page = ceil($total / $limit);

        if ($page > $max_page) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        return [
            'start' => $start,
            'limit' => $limit,
            'max_page' => $max_page,
        ];
    }

    /**
     * @param $objects
     * @return mixed
     */
    public function prepareBackend($objects)
    {
        foreach ($objects->rows as $key => $row) {

            $time = strtotime($row['date_added']);
            $past_time = time() - $time;
            $deactivation_time = (int)\Pyshnov::config()->get('time_deactiv_data') * 3600;
            $percent_time = $past_time / ($deactivation_time / 100);

            if ($row['active']) {
                if ($past_time > ($deactivation_time - 60 * 60 * 24 * 10)) {
                    // Если меньше 10 суток

                    if ($past_time > ($deactivation_time - 60 * 60 * 24)) {
                        // если меньше 24 часов
                        if ($past_time > $deactivation_time) {
                            $status_data = 'Время публикации истекло';
                        } else {
                            $status_data = 'Осталось часов: ' . round(($deactivation_time - $past_time) / 60 / 60);
                        }
                    } else {
                        $status_data = 'Осталось дней: ' . round(($deactivation_time - $past_time) / 60 / 60 / 24);
                    }
                } else {
                    $status_data = 'Осталось дней: ' . round(($deactivation_time - $past_time) / 60 / 60 / 24);
                }
            } else {
                switch ($row['status_data']) {
                    case 1 :
                        $status_data = 'Опубликовано';
                        break;
                    case 2 :
                        $status_data = '<i style="color: red;">Ожидает модерации</i>';
                        break;
                    case 3 :
                        $status_data = 'Снято с публикации';
                        break;
                    case 4 :
                        $status_data = 'Срок размещения истёк';
                        break;
                    case 5 :
                        $status_data = 'Отказано';
                        break;
                    default :
                        $status_data = 'Не известный статус';
                }
            }

            $objects->rows[$key]['date_added_info']['style_width'] = ((100 - round($percent_time, 3)) > 0) ? (100 - round($percent_time, 3)) : 0;
            $objects->rows[$key]['date_added_info']['status_data'] = $status_data;

            $objects->rows[$key]['image'] = unserialize($row['image']);
        }

        return $objects;
    }

    /**
     * @return bool
     */
    public function getObjectById()
    {
        if (!$this->request()->attributes->has('id')) {
            return false;
        }

        $stmt = DB::select('d.*, s.name AS street, c.name AS city_name, u.login AS user_login, u.email AS user_email', DB_PREFIX . '_data d')
            ->leftJoin(DB_PREFIX . '_street s', 'd.street_id', '=', 's.street_id')
            ->leftJoin(DB_PREFIX . '_city c', 'd.city_id', '=', 'c.city_id')
            ->leftJoin(DB_PREFIX . '_user u', 'd.user_id', '=', 'u.user_id')
            ->where('id', '=', $this->request()->attributes->get('id'))
            ->execute();

        if ($rows = $stmt->fetch()) {
            if ($rows['image'])
                $rows['image'] = unserialize($rows['image']);
            else
                $rows['image'] = [];
        }

        return $rows;

    }

    /**
     * Сохраняет данные объекта после редактированя из админки
     *
     * @param $data
     * @return bool
     */
    public function editObject($data)
    {
        if (!$id = $this->request()->attributes->get('id')) {
            return false;
        }

        $active = $data['active'];
        $params = $this->postParam()->all();

        if (!isset($params['active'])) {
            $params['active'] = 0;
        }

        // Получаем список изображений из базы, т.к. возможно часть изображений удалены AJAXом
        $stmt = DB::select('image', DB_PREFIX . '_data')
            ->where('id', '=', $this->request()->attributes->get('id'))
            ->execute();
        $row = $stmt->fetch();
        $params['images'] = $row ? unserialize($row['image']) : [];

        $data = $this->prepareDataBeforeSave($params, $data);

        if ($data['active'] == 1 && ($data['status_data'] == 2 || $data['status_data'] == 3)) {
            $data['status_data'] = 1;
        }

        if ($data['active'] == 0 && $data['status_data'] == 1) {
            $data['status_data'] = 3;
        }

        if (!$this->error()->has()) {
            DB::update($data, DB_PREFIX . '_data')
                ->where('id', '=', $id)
                ->execute();

            if (\Pyshnov::config()->get('moderation_notice')) {
                if ($data['active'] == 1 && $data['active'] != $active && !empty($data['email'])) {

                    $stmt = DB::select('template', DB_PREFIX . '_email')
                        ->where('name', '=', 'edit_data')
                        ->execute();

                    if ($row = $stmt->fetchObject()) {
                        $row->template = stripslashes($row->template);
                        $row->template = str_replace("{%id%}", $id, $row->template);

                        $mail = $this->get('mail');
                        $mail->addAddress($data['email']);
                        $mail->addEmbeddedImage(\Pyshnov::root() . '/uploads/logo-mail.png', 'logo', '', 'base64', 'image/png');
                        $mail->setBody('Сообщение с сайта ' . $this->config()->get('site_name'), $row->template, true);

                        $mail->send();
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param            $params
     * @param array|null $data
     * @param bool       $frontend
     * @return array
     */
    public function prepareDataBeforeSave($params, array $data = null, $frontend = false)
    {
        $result = [];

        if (\Pyshnov::user()->isAnonymous()) {
            $result['user_id'] = 2;
        } else {
            $result['user_id'] = (int)($params['user_id'] ?? \Pyshnov::user()->getId());
        }

        if (empty($params['date_added'])) {
            $result['date_added'] = date('Y-m-d H:i:s', time());
        } else {
            $result['date_added'] = $params['date_added'];
        }

        $result['active'] = isset($params['active']) ? (int)$params['active'] : (\Pyshnov::user()->isAdmin() ? 1 : 0);

        if ($result['active']) {
            $result['status_data'] = 1;
        } else {
            $result['status_data'] = (int)($params['status_data'] ?? 2);
        }

        // Если данные из пдминки
        if (!$frontend) {
            if (!empty($params['reason_refusal'])) {
                $reason_refusal = htmlspecialchars_decode($params['reason_refusal']);
                $result['reason_refusal'] = htmlspecialchars($reason_refusal);
            } else {
                $result['reason_refusal'] = '';
            }
            if (!empty($params['meta_title'])) {
                $title = htmlspecialchars_decode($params['meta_title']);
                $result['meta_title'] = htmlspecialchars($title);
            } else {
                $result['meta_title'] = '';
            }

            if (!empty($params['meta_description'])) {
                $description = htmlspecialchars_decode($params['meta_description']);
                $result['meta_description'] = htmlspecialchars($description);
            } else {
                $result['meta_description'] = '';
            }

            if (!empty($params['meta_keywords'])) {
                $keywords = htmlspecialchars_decode($params['meta_keywords']);
                $result['meta_keywords'] = htmlspecialchars($keywords);
            } else {
                $result['meta_keywords'] = '';
            }

            if (!$params['street'])
                $this->error()->add('Поле "Улица" является обязательным');

            $result['number'] = $params['number'] ?? '';

        } else {
            if (empty($params['street']) && empty($params['address']))
                $this->error()->add('Поле "Адрес" является обязательным');
        }

        $crop = $params['avito_crop'] ?? 0;

        if ($params['topic_id'])
            $result['topic_id'] = (int)$params['topic_id'];
        else
            $this->error()->add('Поле "Тип" является обязательным');

        if ($params['city_id'])
            $result['city_id'] = (int)$params['city_id'];
        else
            $this->error()->add('Поле "Город" является обязательным');

        $result['district_id'] = isset($params['district']) ? (int)$params['district'] : 0;
        $result['metro_id'] = isset($params['metro']) ? (int)$params['metro'] : 0;
        $result['time_metro'] = isset($params['time_metro']) ? intval($params['time_metro']) : 0;
        $result['how_to_get'] = $result['time_metro'] ? (int)$params['how_to_get'] : 0;

        if (!empty($params['text'])) {
            $quotes = ['<br>', '<br />', '<br/>'];
            $text = htmlspecialchars_decode($params['text']);
            $text = str_replace($quotes, "\r\n", $text);
            $result['text'] = htmlspecialchars($text);
        }

        if ($params['price']) {
            $result['price'] = Price::priceDeFormat($params['price']);
        } else {
            $this->error()->add('Не заполнено поле "Цена"');
        }

        $result['lease_period'] = isset($params['lease_period']) ? (int)$params['lease_period'] : 1;

        // Получаем этаж/этажность
        $result['floor'] = isset($params['floor']) ? (int)$params['floor'] : 0;
        $result['floor_count'] = isset($params['floor_count']) ? (int)$params['floor_count'] : 0;

        $result['room_count'] = isset($params['room_count']) ? (int)$params['room_count'] : 0;

        $result['square_all'] = $params['square_all'] ?? '';
        $result['square_live'] = $params['square_live'] ?? '';
        $result['square_rooms'] = $params['square_rooms'] ?? '';
        $result['square_kitchen'] = $params['square_kitchen'] ?? '';

        // Санузел
        $result['bathroom'] = isset($params['bathroom']) ? (int)$params['bathroom'] : 0;

        // Чекбоксы
        $result['furniture'] = isset($params['furniture']) ? (int)$params['furniture'] : 0;
        $result['refrigerator'] = isset($params['refrigerator']) ? (int)$params['refrigerator'] : 0;
        $result['washing_machine'] = isset($params['washing_machine']) ? (int)$params['washing_machine'] : 0;
        $result['television'] = isset($params['television']) ? (int)$params['television'] : 0;
        $result['is_telephone'] = isset($params['is_telephone']) ? (int)$params['is_telephone'] : 0;
        $result['internet'] = isset($params['internet']) ? (int)$params['internet'] : 0;
        $result['children'] = isset($params['children']) ? (int)$params['children'] : 0;
        $result['animal'] = isset($params['animal']) ? (int)$params['animal'] : 0;

        // Получаем имя
        if (isset($params['fio']) && $params['fio']) {
            $fio = htmlspecialchars_decode($params['fio']);
            $result['fio'] = htmlspecialchars($fio);
        } else {
            $result['fio'] = 'Собственник';
        }

        // Получаем email
        if (!empty($params['email'])) {
            $result['email'] = filter_var($params['email'], FILTER_VALIDATE_EMAIL);
            if (!$result['email'])
                $this->error()->add('Не верный формат email');
        }

        // Получаем телефон
        if (!empty($params['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $params['phone']);
            if ($this->get('module_handler')->isEnable('blacklist')) {
                $phone = '7' . substr($phone, -10, 10);
                $row = DB::select('id', DB_PREFIX . '_blacklist')
                    ->where('phone', '=', $phone)
                    ->execute()
                    ->rowCount();
                if ($row)
                    $this->error()->add('<b>Номер числится в черном списке и не может быть добавлен на сайт</b>');
            }
            $result['phone'] = $phone;
        } else {
            $this->error()->add('Не узаказн номер телефона');
        }

        // Если все поля заполнены правильно
        if (!$this->error()->has()) {

            if ($frontend) {
                if (isset($params['street_id'])) {
                    $result['street_id'] = $params['street_id'];
                    $result['number'] = $params['number'] ?? '';
                } else {
                    $result['address'] = $params['address'];
                }

            } else {
                if ($params['street_id']) {
                    $result['street_id'] = $params['street_id'];
                } else {
                    if ($result['city_id']) {
                        $street_params = [
                            'city_id' => $result['city_id'],
                            'name' => $params['street'],
                        ];
                        $stmt = DB::insert($street_params, DB_PREFIX . '_street')->execute(true);
                        $result['street_id'] = $stmt;
                    }
                }

                if ($this->config()->get('geodata_enable')) {

                    if ($params['do'] == 'edit') {
                        // Если координаты были изменены в ручную
                        if ($params['geo_lat'] != $data['geo_lat'] || $params['geo_lng'] != $data['geo_lng']) {
                            $result['geo_lat'] = $params['geo_lat'];
                            $result['geo_lng'] = $params['geo_lng'];
                        } else {
                            // Если адресс изменился
                            if ($result['street_id'] != $data['street_id'] || $result['number'] != $data['number']) {

                                $geocode = $this->geocode($result['city_id'], $params['street'], $result['number']);

                                if ($geocode) {
                                    $result['geo_lat'] = $geocode['geo_lat'];
                                    $result['geo_lng'] = $geocode['geo_lng'];
                                }
                            }
                        }
                    } else {

                        $geocode = $this->geocode($result['city_id'], $params['street'], $result['number']);

                        if ($geocode) {
                            $result['geo_lat'] = $geocode['geo_lat'];
                            $result['geo_lng'] = $geocode['geo_lng'];
                        }
                    }
                }
            }

            if ($this->session()->has('img_add')) {
                $new_img = unserialize($this->session()->get('img_add'));
                if (array_key_exists($params['key'], $new_img)) {
                    $upload = new Upload();
                    $result['image'] = $upload->appendUpload($new_img[$params['key']], $crop, $params['images']);
                    // Удаляем из сессии изображения
                    $this->session()->remove('img_add');
                }
            } else {
                $result['image'] = !empty($params['images']) ? serialize($params['images']) : '';
            }
        }

        return $result;
    }

    public function geocode($city_id, $street, $number)
    {
        $city = DB::select('name', DB_PREFIX . '_city')
            ->where('city_id', '=', $city_id)
            ->limit(1)
            ->execute()
            ->fetch();

        $address = $city['name'] . ', ' . $street . ($number ? ' ' . $number : '');

        $geocode = new Geocode();
        $geodata = $geocode->setQuery($address)->setLimit(1)->load();

        if ($geodata) {

            return [
                'geo_lat' => $geocode->getLatitude(),
                'geo_lng' => $geocode->getLongitude()
            ];
        }

        return false;
    }

    public function newObject($frontend = false)
    {
        $params = $this->postParam()->all();

        $params['images'] = [];

        $data = $this->prepareDataBeforeSave($params, null, $frontend);

        if (!$this->error()->has()) {

            $id = DB::insert($data, DB_PREFIX . '_data')->execute(true);

            if ($frontend) {

                $stmt = DB::select('template', DB_PREFIX . '_email')
                    ->where('name', '=', 'new_data')
                    ->execute();

                if ($row = $stmt->fetchObject()) {
                    $row->template = stripslashes($row->template);
                    $row->template = str_replace("{%id%}", $id, $row->template);

                    $mail = $this->get('mail');
                    $mail->addAddress($this->config()->get('email_notice_info'));
                    $mail->setBody('Сообщение с сайта ' . $this->config()->get('site_name'), $row->template, true);

                    $mail->send();
                }
            }

            // TODO Нужно доделать автопостинг твиттер
            //$test = new twitterSender();
            //$test->postTweet('testtovaya zapis');

            return $id;
        } else
            return false;
    }

    /**
     * Вернет спис жалоб на объявления
     *
     * @return mixed
     */
    public function getComplaint()
    {
        $query = DB::select('*', DB_PREFIX . '_complaint')
            ->orderBy('id')
            ->execute()->fetchAll();

        return $query;
    }
}