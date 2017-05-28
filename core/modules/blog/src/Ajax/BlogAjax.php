<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\blog\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class BlogAjax extends AjaxResponse
{
    public function runAction()
    {
        $res = false;

        if ($action = $this->request()->get('action', false)) {
            if (method_exists($this, $action)) {
                $res = $this->$action();
            }
        }

        return $this->render($res);
    }

    public function updateStatus()
    {
        $id = $this->request()->request->get('id');
        $value = $this->request()->request->get('value');

        $stmt = DB::update(['active' => $value], DB_PREFIX . '_blog')->where('id', '=', $id)->execute();

        if ($stmt) {
            $this->data = [
                'title' => $value ? 'Деактивировать' : 'Активировать',
                'value' => $value ? '0' : '1'
            ];

            return true;
        }

        return false;
    }

    public function remove()
    {
        $id = $this->request()->query->get('id');
        $table = $this->request()->query->get('table');

        if ($id && $table) {
            if ($table == 'blog') {
                $stmt = DB::select('image', DB_PREFIX . '_blog')
                    ->where('id', '=', $id)->limit(1)->execute();
                if ($row = $stmt->fetch()) {
                    DB::delete(DB_PREFIX . '_router')
                        ->where('name', '=', 'blog.article' . $id)
                        ->execute();
                    if ($row['image']) {
                        $path = \Pyshnov::root() . '/uploads/blog/';
                        @unlink($path . $row['image']);
                        @unlink($path . 'thumbs/' . $row['image']);
                    }
                }
            } elseif ($table == 'blog_category') {
                DB::delete(DB_PREFIX . '_router')
                    ->where('name', '=', 'blog.category' . $id)
                    ->execute();
            }

            DB::delete(DB_PREFIX . '_' . $table)->where('id', '=', $id)->execute();

            return true;
        }

        return false;
    }

    public function actionEntity()
    {
        $id = $this->request()->request->get('id');
        $action = $this->request()->request->get('do');

        switch ($action) {
            case 'activate':
                DB::update(['active' => 1], DB_PREFIX . '_blog')->whereIn('id', $id)->execute();
                break;
            case 'deactivate':
                DB::update(['active' => 0], DB_PREFIX . '_blog')->whereIn('id', $id)->execute();
                break;
            case 'delete_selected':

                $stmt = DB::select('image', DB_PREFIX . '_blog')
                    ->whereIn('id', $id)
                    ->execute();

                if ($rows = $stmt->fetchAll()) {
                    $path = \Pyshnov::root() . '/uploads/blog/';
                    foreach ($rows as $row) {
                        if ($row['image']) {
                            @unlink($path . $row['image']);
                            @unlink($path . 'thumbs/' . $row['image']);
                        }
                    }
                }

                DB::delete(DB_PREFIX . '_blog')->whereIn('id', $id)->execute();

                break;
            default:
                return false;
        }

        return true;
    }
}