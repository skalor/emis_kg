<?php
namespace MonGeneratedStatisticReports\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class MonGeneratedStatisticReportsController extends PageController
{
    public $helpers = ['MonGeneratedStatisticReports.Signing'];
    public $user = ['institution_id' => null];

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('MonGeneratedStatisticReports.MonGeneratedStatisticReports');
        $this->loadModel('MonStatisticReports.MonStatisticReports');
        
        if ($this->Auth->user('id')) {
            $this->user = $this->Auth->user();
            $staff = TableRegistry::get('Institution.Staff')->find()->where(['staff_id' => $this->user['id']])->first();
            $this->user['institution_id'] = $staff ? $staff->get('institution_id') : null;
            $this->loadComponent('MonGeneratedStatisticReports.Sign');
            $this->Security->config('unlockedActions', ['sign']);
        }
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;
        $page->setHeader(__('MonGeneratedStatisticReports'));
        $page->addCrumb('MonGeneratedStatisticReports', ['plugin' => 'MonGeneratedStatisticReports', 'controller' => 'MonGeneratedStatisticReports', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }
        
        $page->enable(['download']);
        $page->exclude(['params']);
        $selectPickerAttrs = [
            'class' => 'selectpicker',
            'data-size' => '15',
            'data-dropup-auto' => 'false',
            'data-live-search' => 'true'
        ];
        
        $report = $page->get('mon_statistic_report_id')->setControlType('select')->setAttributes($selectPickerAttrs)->setSortable(true);
        $institution = $page->get('institution_id')->setControlType('hidden')->setAttributes($selectPickerAttrs)->setSortable(true);
        $page->get('academic_period_id')->setControlType('select')->setAttributes($selectPickerAttrs)->setSortable(true);
        $page->get('file_type')->setControlType('select')->setOptions(['PDF' => 'PDF', 'Excel' => 'Excel'])->setSortable(true);
        $page->get('file_content_hash')->setAttributes('fileNameField', 'file_name_hash');
        $page->get('upload')->setSortable(true);
        $page->get('is_signed')->setSortable(true);
        $page->get('signed_by_id')->setSortable(true);
        $this->set('controllerName', $this->name);
        
        $reportOptions = $this->getReportOptions();
        if ($report && isset($reportOptions['list'])) {
            $report->setOptions($reportOptions['list']);
        }
        
        if ($institution && $this->user['institution_id']) {
            $institution->setValue($this->user['institution_id']);
        }
    }

    public function getReportOptions(?int $periodId = 0)
    {
        $institution = TableRegistry::get('Institution.Institutions')->find()->where(['id'=>$this->user['institution_id']])->first();
        $period = TableRegistry::get('AcademicPeriod.AcademicPeriods')->find()->where(['id'=>$periodId])->first();
        $reportsWhere = [
            'for_date >=' => $period ? $period->start_date : null,
            'for_date <=' => $period ? $period->end_date : null,
            'params >' => ''
        ];
        
        $reports = $this->MonStatisticReports->find('all')->where($reportsWhere)->all()->toArray();
        $tempResult = [];
        if ($reports && $institution) {
            foreach ($reports as $report) {
                $params = isset($report['params']) && $report['params'] ? unserialize($report['params']) : [];
                if (
                    isset($params['institutionTypesIds']) && $params['institutionTypesIds'] && $institution
                        && !in_array($institution->get('institution_type_id'), $params['institutionTypesIds'])
                    || isset($params['areasIds']) && $params['areasIds'] && $institution
                        && !in_array($institution->get('area_id'), $params['areasIds'])
                    || isset($params['areaAdministrativesIds']) && $params['areaAdministrativesIds'] && $institution
                        && !in_array($institution->get('area_administrative_id'), $params['areaAdministrativesIds'])
                ) {
                    continue;
                }
                
                $tempResult[$report->id] = $report->name;
            }
        }
        
        return ['list' => $tempResult];
    }

    public function checkGeneratingProgress($id)
    {
        $entity = $this->MonGeneratedStatisticReports->get($id);
        $this->response->type('ajax');
        $params = $entity->params ? unserialize($entity->params) : [];
        $status = isset($params['status']) ? $params['status'] : 0;
        $this->response->body(json_encode($status));
        
        return $this->response;
    }

    public function sign()
    {
        $this->Sign->sign();
    }
    
    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderUpload'] = 'onRenderUpload';
        
        return $events;
    }

    public function onRenderUpload(Event $event, Entity $entity)
    {
        $action = $this->request->action;
        $page = $this->Page;
        if (in_array($action, ['add', 'edit'])) {
            $session = $this->request->session();
            if ($session->check('alert') && $entity->submit === 'reload') {
                $session->delete('alert');
            }
            
            $onchange = ['onchange' => '$("#reload").click();'];
            $page->get('upload')->setControlType('select')->setOptions([__('No'), __('Yes')], false)->setAttributes($onchange);
            $page->get('file_content')->setRequired(true);
            
            if (!$entity->upload) {
                $page->exclude(['file_content']);
            }
            
            $periodId = $entity->academic_period_id;
            $period = $page->get('academic_period_id')->setAttributes($onchange);
            if (!$entity->academic_period_id) {
                $periodId = TableRegistry::get('AcademicPeriod.AcademicPeriods')->getCurrent();
                $period->setValue($periodId);
            }
            
            $reportOptions = $this->getReportOptions($periodId);
            $page->get('mon_statistic_report_id')->setOptions($reportOptions['list']);
        }
    }

    public function index()
    {
        $page = $this->Page;
        $page->exclude(['file_name', 'file_name_hash', 'file_content', 'file_content_hash']);
        $user = $this->Auth->user();
        $staff = TableRegistry::get('Institution.Staff')->find()->where(['staff_id' => $user['id']])->first();
        $page->setQueryOption('order', [$this->MonGeneratedStatisticReports->aliasField('created') => 'DESC']);
        $page->addNew('created_on')->setDisplayFrom('created');
        
        if (!$user['super_admin'] && !$staff) {
            $page->setQueryString('institution_id', 0, true);
        } else if ($staff) {
            $page->setQueryString('institution_id', $staff->get('institution_id'), true);
        }
        
        parent::index();
        $this->viewBuilder()->template('Page/index');
    }

    public function view($id)
    {
        parent::view($id);
        $page = $this->Page;
        $page->exclude(['file_name', 'file_name_hash']);
        $page->get('institution_id')->setControlType('select');
        $primaryKeyValue = $page->decode($id);
        $table = $page->getMainTable();
        
        $params = @unserialize($table->get($primaryKeyValue)->params);
        if (isset($params['start_datetime'])) {
            $page->addNew('process_start_date')->setControlType('string')->setValue($params['start_datetime']);
        }
        if (isset($params['end_datetime'])) {
            $page->addNew('process_end_date')->setControlType('string')->setValue($params['end_datetime']);
        }
    }

    public function add()
    {
        parent::add();
        $page = $this->Page;
        $page->exclude(['file_name', 'file_name_hash', 'file_content_hash', 'signed_by_id']);
        $page->move('academic_period_id')->first();
        $this->viewBuilder()->template('MonFields.Page/add');
    }
    
    public function edit($id)
    {
        parent::edit($id);
        $page = $this->Page;
        $page->exclude(['file_name', 'file_name_hash', 'file_content_hash', 'signed_by_id']);
        $page->move('academic_period_id')->first();
        $this->viewBuilder()->template('MonFields.Page/add');
    }
    
    public function delete($id)
    {
        parent::delete($id);
        $page = $this->Page;
        $page->exclude(['file_content', 'file_content_hash']);
    }
}
