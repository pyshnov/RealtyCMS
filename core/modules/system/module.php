<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function system_template_pre_process(&$variables) {

    /*$stmt = \Pyshnov\Core\DB\DB::select('id, image', DB_PREFIX . '_data')->execute()->fetchAll();

    foreach ($stmt as $item) {

        if ($item['image']) {
            $images = unserialize($item['image']);

            $data = [];

            foreach ($images as $image) {
                if (is_string($image)) {
                    $data[] = [
                        'name' => $image,
                        'alt' => ''
                    ];
                } else {
                    $data[] = [
                        'name' => $image['name'],
                        'alt' => ''
                    ];
                }
            }

            \Pyshnov\Core\DB\DB::update(['image' => serialize($data)], DB_PREFIX . '_data')->where('id', '=', $item['id'])->execute();

            var_dump($data);
        }

    }*/

}