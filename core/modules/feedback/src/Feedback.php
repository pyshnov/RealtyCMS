<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\feedback;


use Pyshnov\Core\Controller\BaseController;

class Feedback extends BaseController
{

    protected $params;

    public function init($_title)
    {
        $data = [];

        return $this->render($data, 'feedback');
    }

}