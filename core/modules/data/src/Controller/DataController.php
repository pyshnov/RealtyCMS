<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\data\Form\DataForm;
use Pyshnov\data\Model\DataModel;
use Symfony\Component\HttpFoundation\Response;

class DataController extends BaseController
{
    public function execute()
    {

    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminData($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $data['breadcrumb'] = $this->breadcrumb()->setLinks([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['page'] = $model->prepareBackend($model->getDataAll());
        $data['count_active'] = $model->countActive();
        $data['count_un_active'] = $model->countUnActive();
        $data['count_moderation'] = $model->countModeration();
        $data['params'] = $this->request()->query->all();

        return $this->render($data);
    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminEdit($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => '/admin/data/',
            'Редактировать' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        if ($object = $model->getObjectById()) {
            $data['object'] = $object;
        } else {
            return $this->render([], 'not_object');
        }

        if ('edit' == $this->request()->request->get('do')) {
            if ($model->editObject($data['object'])) {
                header("Location: /admin/data/");
                exit();
            }
        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;

        return $this->render($data);
    }

    public function adminAdd($_title)
    {

        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => '/admin/data/',
            'Новое объявление' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        if ('add' == $this->request()->request->get('do')) {
            if ($model->newObject()) {
                $this->setFlash('Объект успешно добавлен в базу данных', true);
                header("Location: /admin/data/");
                exit();
            }
        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;

        $data['request'] = $this->postParam()->all();

        return $this->render($data);
    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminComplaint($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Жалобы' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['complaint'] = $model->getComplaint();

        return $this->render($data);
    }

    public function location()
    {
       // $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Жалобы' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data = [];

        //$data['complaint'] = $model->getComplaint();

        return $this->render($data, 'data_location');
    }
}