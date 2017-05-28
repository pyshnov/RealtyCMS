<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\system\Model\SystemModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SystemController extends BaseController
{
    /**
     * Главная админки
     *
     * @return Response
     */
    public function adminMain()
    {

        $this->template()->setMetaTitle($this->t('system.control_panel'));

        return $this->render([], 'admin_main');
    }

    public function adminModule($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('system.extensions') => ''
        ]);

        $model = new SystemModel();

        $model->setContainer($this->getContainer());

        $data['modules'] = $model->getModules();

        return $this->render($data);
    }

    /**
     * Главная сайта
     *
     * @return Response
     */
    public function main()
    {
        return $this->render([], 'main');
    }

    /**
     * @return Response
     */
    public function error404()
    {
        $this->template()->setMetaTitle($this->t('system.page_not_found') . ' | ' . $this->config()->get('site_name'));
        return $this->render([], '404', new Response('', 404));
    }

    /**
     * Страница "доступ запрещен"
     *
     * @return Response
     */
    public function accessDenied()
    {
        if ($this->isAjax()) {
            return new JsonResponse('', 403);
        }

        $this->template()->setMetaTitle('403 — ' . $this->config()->get('site_name'));
        $this->template()->setThemeType('global');
        return $this->render([], 'access_denied', new Response('', 403));
    }

}