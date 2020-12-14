<?php
namespace MonStatisticReports\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class MonStatisticReportsController extends PageController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;
        $page->addCrumb('MonStatisticReports', ['plugin' => 'MonStatisticReports', 'controller' => 'MonStatisticReports', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('MonStatisticReports'));

        $selectPickerAttrs = [
            'class' => 'selectpicker',
            'data-size' => '15',
            'data-dropup-auto' => 'false',
            'data-live-search' => 'true',
            'data-actions-box' => 'true',
            'selectAllText' => __('Select all'),
            'deselectAllText' => __('Deselect all'),
        ];

        $page->get('params')->setControlType('hidden');
        $page->get('template_id')->setControlType('select')->setAttributes($selectPickerAttrs)->setSortable(true);
        if (in_array($action, ['add', 'edit'])) {
            $page->addNew('areas')->setControlType('select')->setOptions($this->getList('Area.Areas'), false)
                ->setAttributes($selectPickerAttrs)->setAttributes('multiple', true)->setSortable(true);
            $page->addNew('area_administratives')->setControlType('select')->setOptions($this->getList('Area.AreaAdministratives'), false)
                ->setAttributes($selectPickerAttrs)->setAttributes('multiple', true)->setSortable(true);
            $page->addNew('institution_types')->setControlType('select')->setOptions($this->getList('Institution.Types'), false)
                ->setAttributes($selectPickerAttrs)->setAttributes('multiple', true)->setSortable(true);
        }
    }
    
    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderParams'] = 'onRenderParams';

        return $events;
    }
    
    public function onRenderParams(Event $event, Entity $entity)
    {
        $page = $this->Page;
        $action = $this->request->action;
        $params = $entity->params ? unserialize($entity->params) : [];
        
        switch ($action) {
            case 'edit':
                isset($params['areasIds']) && $params['areasIds']
                    ? $page->get('areas')->setValue($params['areasIds'])
                    : null;
                isset($params['areaAdministrativesIds']) && $params['areaAdministrativesIds']
                    ? $page->get('area_administratives')->setValue($params['areaAdministrativesIds'])
                    : null;
                isset($params['institutionTypesIds']) && $params['institutionTypesIds']
                    ? $page->get('institution_types')->setValue($params['institutionTypesIds'])
                    : null;
                break;
            case 'view':
                $areas = $page->addNew('areas')->setControlType('string');
                $areaAdministratives = $page->addNew('area_administratives')->setControlType('string');
                $institutionTypes = $page->addNew('institution_types')->setControlType('string');

                isset($params['areasIds']) && $params['areasIds']
                    ? $areas->setValue(implode(', ', $this->getList('Area.Areas', ['id IN' => $params['areasIds']])))
                    : null;
                isset($params['areaAdministrativesIds']) && $params['areaAdministrativesIds']
                    ? $areaAdministratives->setValue(implode(', ', $this->getList('Area.AreaAdministratives', ['id IN' => $params['areaAdministrativesIds']])))
                    : null;
                isset($params['institutionTypesIds']) && $params['institutionTypesIds']
                    ? $institutionTypes->setValue(implode(', ', $this->getList('Institution.Types', ['id IN' => $params['institutionTypesIds']])))
                    : null;
                break;
        }
    }
    
    public function index()
    {
        parent::index();
        $this->Page->exclude(['params']);
    }
    
    public function getList(string $model, array $where = [])
    {
        $table = TableRegistry::get($model);
        if (!$table) {
            return [];
        }
        
        return $table->find('list')->where($where)->all()->toArray();;
    }
}
