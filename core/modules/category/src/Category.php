<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\category;


use Pyshnov\Core\DB\DB;
use Pyshnov\form\Element\Select;
use Pyshnov\form\Form;

class Category
{

    protected $category;
    protected $aliases;
    protected $children;
    protected $links;

    public function __construct()
    {
        $load_active = \Pyshnov::config()->get('active_topic');

        $smtp = DB::select('*', DB_PREFIX . '_topic')->orderBy('`order`');
        if ($load_active)
            $smtp->where('active', '=', 1);
        $rows = $smtp->execute()->fetchAll();

        $category = [];
        $children = [];

        foreach ($rows as $row) {
            $category[$row['id']] = $row;
        }

        // перебираем и если предок не активен, тогда потомков не используем
        foreach ($category as $item) {
            if (isset($category[$item['parent_id']]) || $item['parent_id'] === 0) {
                $children[$item['parent_id']][] = $item['id'];
            } else {
                unset($category[$item['id']]);
            }
        }

        $test = $this->prepareUrl($category);

        foreach ($category as $k => $v) {
            $this->aliases[$k] = $v['url'];
            $link = '/' . $test[$v['id']] . '/';
            $this->links[$k] = $link;
            $category[$k]['url'] = $link;
        }

        $this->category = $category;
        $this->children = $children;

        //var_dump($children);


    }

    public function prepareUrl($cat)
    {
        $ret = [];
        $_ret = [];

        $categories = [];
        $items = [];
        $points = [];

        foreach ($cat as $id => $v) {
            $categories[$id] = $v['url']; // список id = url
            $items[$id] = $v['parent_id']; // список id = уровень вложенности
            $points[] = $id; // список id
        }

        if (count($points)) {
            foreach ($points as $p) {
                $chain = [];
                $chain[] = $categories[$p];

                $this->appendParent($p, $items, $chain, $categories);
                $_ret[$p]['chain_parts'] = $chain;
            }

            foreach ($_ret as $k => $r) {
                $ret[$k] = trim(implode('/', $r['chain_parts']), '/');
            }
        }

        return $ret;
    }

    /**
     * Ищет транслитерированный урл предка для конкретного элемента
     *
     * @param $child_id
     * @param $items
     * @param $chain
     * @param $categories
     */
    private function appendParent($child_id, &$items, &$chain, $categories)
    {
        if ((int)$items[$child_id] !== 0 && array_key_exists($items[$child_id], $categories)) {
            array_unshift($chain, $categories[$items[$child_id]]);
            $this->appendParent($items[$child_id], $items, $chain, $categories);
        }
    }

    /**
     * Вернет массив категорий
     * Результат будет зависить от настроек программы
     * если активирован параметр 'active_topic'
     * будет возвращены только активные категории
     *
     * @param null|int $id
     *
     * @return array|bool
     */
    public function getCategory($id = null)
    {
        if (!is_null($id)) {
            return $this->category[$id] ?? false;
        }

        return $this->category;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Вернет массив всех категорий
     *
     * @return array
     */
    public function getDbCategoryAll()
    {
        $stmt = DB::select(['id', 'name', 'parent_id'], DB_PREFIX . '_topic')->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param int          $selected
     * @param array        $parent_id
     * @param array|string $attr
     * @param null         $zero_name
     *
     * @return Select
     */
    public function getTopicSelect($selected = 0, $parent_id = 0, $attr = [], $zero_name = null)
    {
        if (null === $zero_name) {
            $zero_name = \Pyshnov::t('system.topic_zero_select');
        }

        if (is_string($attr)) {
            $attr = (array)$attr;
        }

        $options = [];

        if (!is_array($parent_id)) {
            $parent_id = (array)$parent_id;
        }

        $tree = $this->getChildren();

        foreach ($parent_id as $item) {
            $options = $options + $this->level($tree, $item);
        }

        $form = new Form();

        $el = $form->addSelect('topic_id')
            ->setId('topicId')
            ->setAttribute($attr)
            ->setOptions($options)
            ->addAttribute('title', $zero_name)
            ->setValue($selected);

        return $el;
    }

    /**
     * @param     $tree
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public function level($tree, $parent_id = 0, $level = 0)
    {
        $catalog = $this->category;

        $option = [];
        if (isset($tree[$parent_id])) {
            $lev = '';
            for ($i = 0; $i < $level; $i++)
                $lev .= '&nbsp;&nbsp; ';
            foreach ($tree[$parent_id] as $id) {
                $test = $catalog[$id];
                $option[$test['id']] = $lev . $test['name'];
                foreach ($this->level($tree, $test['id'], $level + 1) as $key => $value)
                    $option[$key] = $value;
            }
        } else
            return [];

        return $option;
    }

    /**
     * Вернет всех детей принадлежащих указанной категории
     *
     * @param $topic_id
     *
     * @return array
     */
    public function allChildrenCategories($topic_id)
    {
        $children = $this->getChildren();

        $child = [];

        if (isset($children[$topic_id])) {

            foreach ($children[$topic_id] as $id) {

                $child[] = $id;

                foreach ($this->allChildrenCategories($id) as $value)
                    $child[] = $value;;
            }
        } else {
            return [];
        }

        return $child;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases ?? [];
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

}