<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\user;


class User
{
    /**
     * @var object
     */
    private $user;

    private $groupName = [
        0 => 'Анонимный',
        1 => 'Администратор',
        2 => 'Модератор',
        3 => 'Риелтор',
        4 => 'Зарегестрированный'
    ];

    /**
     * @return object
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getId():int
    {
        return (int)$this->user->user_id;
    }

    /**
     * @return int
     */
    public function getCroupId():int
    {
        return $this->user->group_id;
    }

    /**
     * @return string
     */
    public function getLogin():string
    {
        return $this->user->login;
    }

    public function getEmail()
    {
        return $this->user->email;
    }

    public function getFio()
    {
        return $this->user->fio;
    }

    public function getSystemName()
    {
        return $this->user->system_name;
    }

    /**
     * @return bool
     */
    public function isAuthenticated():bool
    {
        return $this->getId() > 0;
    }

    /**
     * @return bool
     */
    public function isAdmin():bool
    {
        return $this->getCroupId() === 1;
    }

    /**
     * @return bool
     */
    public function isModerator():bool
    {
        return $this->getCroupId() == 2;
    }

    /**
     * @return bool
     */
    public function isAnonymous():bool
    {
        return $this->getId() == 0;
    }

    public function setAnonymousUser()
    {
        $user = new \stdClass();

        $user->user_id = 0;
        $user->group_id = 0;
        $user->system_name = 'anonymous';
        $user->login = 'anonymous';
        $user->fio = 'Anonymous';

        $this->setUser($user);
    }
}