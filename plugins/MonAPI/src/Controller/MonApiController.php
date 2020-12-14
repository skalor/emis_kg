<?php
namespace MonAPI\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class MonApiController extends PageController
{
    public $institutionsTypes;

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('MonAPI.Restful');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;

        $page->addCrumb('MonAPI', ['plugin' => 'MonAPI', 'controller' => 'MonApi', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('MonAPI'));
        $page->get('params')->setControlType('hidden');

        $this->institutionsTypes = TableRegistry::get('Institution.Types')->find('list')->all()->toArray();

        if (in_array($action, ['add', 'edit'])) {
            $page->get('user_id')->setControlType('hidden');
            $page->addNew('username')->setControlType('string')->setMaxlength(100)->setRequired(true);
            $page->move('username')->after('name');

            $page->addNew('models')->setControlType('select')->setOptions($this->Restful->models, false)->setAttributes([
                'data-size' => 15,
                'data-live-search' => true,
                'data-dropup-auto' => false,
                'data-actions-box' => true,
                'selectAllText' => __('Select all'),
                'deselectAllText' => __('Deselect all'),
                'class' => 'selectpicker'
            ])->setAttributes('multiple', true);

            $page->addNew('actions')->setControlType('select')->setOptions($this->Restful->actions, false)->setAttributes([
                'data-size' => 15,
                'data-live-search' => true,
                'data-dropup-auto' => false,
                'data-actions-box' => true,
                'selectAllText' => __('Select all'),
                'deselectAllText' => __('Deselect all'),
                'class' => 'selectpicker'
            ])->setAttributes('multiple', true);

            $page->addNew('institutions_types')->setControlType('select')->setOptions($this->institutionsTypes, false)->setAttributes([
                'data-size' => 15,
                'data-live-search' => true,
                'data-dropup-auto' => false,
                'data-actions-box' => true,
                'selectAllText' => __('Select all'),
                'deselectAllText' => __('Deselect all'),
                'class' => 'selectpicker'
            ])->setAttributes('multiple', true);

            $page->addNew('institutions_codes')->setControlType('string')->setMaxlength(250)->setAttributes([
                'class' => 'tokenfield',
                'placeholder' => __('Type institution code or * and hit enter')
            ]);
        }

    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderUserId'] = 'onRenderUserId';
        $events['Controller.Page.onRenderParams'] = 'onRenderParams';

        return $events;
    }

    public function onRenderUserId(Event $event, Entity $entity)
    {
        if ($this->request->action === 'edit' && $entity->user) {
            $this->Page->get('username')->setValue($entity->user->get('username'));
        }
    }

    public function onRenderParams(Event $event, Entity $entity)
    {
        $page = $this->Page;
        $action = $this->request->action;
        $params = $entity->params ? unserialize($entity->params) : [];

        if ($params && $action === 'edit') {
            $params['modelsIds'] ? $page->get('models')->setValue($params['modelsIds']) : null;
            $params['actionsIds'] ? $page->get('actions')->setValue($params['actionsIds']) : null;
            $params['institutionsTypesIds'] ? $page->get('institutions_types')->setValue($params['institutionsTypesIds']) : null;
            $params['institutionsCodes'] ? $page->get('institutions_codes')->setValue($params['institutionsCodes']) : null;
        }

        if ($params && $action === 'view') {
            $models = $page->addNew('models')->setControlType('string');
            $actions = $page->addNew('actions')->setControlType('string');
            $institutionsTypes = $page->addNew('institutions_types')->setControlType('string');
            $institutionsCodes = $page->addNew('institutions_codes')->setControlType('string');

            $params['modelsIds'] ? $models->setValue(implode(', ', $this->Restful->getValues($this->Restful->models, $params['modelsIds']))) : null;
            $params['actionsIds'] ? $actions->setValue(implode(', ', $this->Restful->getValues($this->Restful->actions, $params['actionsIds']))) : null;
            $params['institutionsTypesIds'] ? $institutionsTypes->setValue(implode(', ', $this->Restful->getValues($this->institutionsTypes, $params['institutionsTypesIds']))) : null;
            $params['institutionsCodes'] ? $institutionsCodes->setValue($params['institutionsCodes']) : null;
        }
    }

    public function index()
    {
        parent::index();
        $this->Page->exclude(['params']);
    }
}