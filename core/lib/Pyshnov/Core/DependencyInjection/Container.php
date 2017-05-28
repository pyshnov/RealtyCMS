<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\DependencyInjection;


class Container implements ContainerInterface
{

    private $services;

    public function __construct()
    {
        $this->services = [];
    }

    public function set($id, $service)
    {
        $id = strtolower($id);

        $this->services[$id] = $service;
    }

}