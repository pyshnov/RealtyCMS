<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\user\Controller;


use Pyshnov\Core\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    public function signIn()
    {
        $this->template()->setThemeType('global');

        $data['blocked'] = false;

        if ($this->session()->has('blockTimeStart')) {
            $data['blocked'] = true;
            return $this->render($data, 'user_signin', new Response('', 403));
        }

        if ($this->request()->request->get('do') == 'enter') {
            $login = $this->request()->request->get('login');
            $password = $this->request()->request->get('password');
            $rememberme = $this->request()->request->has('rememberme');

            if ($login && $password) {

                if($this->get('user.auth')->authorize($login, $password, $rememberme)) {
                    header('Location: ' . $this->request()->request->get('returnUrl'));
                    exit();
                } else {
                    if($this->session()->has('blockTimeStart')) {
                        $data['blocked'] = true;
                    } else {
                        $this->error()->set('Неверный логин или пароль');
                    }
                }
            }
        }

        $data['error'] = $this->error()->get();

        return $this->render($data, 'user_signin', new Response('', 403));
    }
}