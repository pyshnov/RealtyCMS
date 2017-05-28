<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\user\Ajax;

use Pyshnov\Core\Ajax\AjaxResponse;

class UserAjax extends AjaxResponse
{
    public function signIn()
    {
        $res = false;

        if ($s = $this->getPostParam('s')) {

            parse_str($s, $arr);

            if ($arr['email'] && $arr['pass']) {

                $rememberme = isset($arr['rememberme']);

                if($this->get('user.auth')->authorize($arr['email'], $arr['pass'], $rememberme)) {
                    $res = true;
                } else {
                    if($this->session()->has('blockTimeStart')) {
                        $this->setData('blocked');
                        $this->setMessageError('Привышен лимин попыток авторизации. Авторизация с вашего ip временно заморожена.');
                    } else {
                        $this->setMessageError('Неверный логин или пароль');
                    }
                }
            }
        }

        return $this->render($res);
    }
}