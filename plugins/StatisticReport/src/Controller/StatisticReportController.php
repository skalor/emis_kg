<?php
namespace StatisticReport\Controller;

use App\Controller\PageController;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;


use TemplateReport\Controller\TemplateReport;

/**
 * Faq Controller
 *
 * @property \StatisticReport\Model\Table\FaqTable $Faq
 */
class StatisticReportController extends PageController
{

    public function initialize()
    {
        parent::initialize();
    }

    private function attachAngularModules()
    {
        $action = $this->request->action;

        switch ($action) {
            case 'edit':
                $this->Angular->addModules([
                    'StatisticReport',
                ]);
                break;
        }
    }

    public function StatisticReport() {
        $this->set('ngController', 'StatisticReportCtrl as StatisticReportController');
    }

    private function overrideTemplatePath(){

        $page = $this->Page;
        $request = $this->request;
        $action = $request->action;
        $ext = $this->request->params['_ext'];

        if ($ext != 'json') {
            if ($request->is(['put', 'post'])) {
                $page->showElements(true);
            }

            $this->set('menuItemSelected', [$this->name]);

            if ($page->isAutoRender() && in_array($action, ['index', 'view', 'add', 'edit', 'delete'])) {
                $viewFile = 'Page.Page/' . $action;
                $this->viewBuilder()->template($viewFile);
            }
        }
    }

    public function view($id) {
        parent::view($id);
        $this->render('StatisticReport.view');
    }

    public function edit($id) {
        parent::edit($id);
        $this->render('StatisticReport.edit');
    }

    public function api() {
        $key = $this->request->query['key'];

        switch ($key) {
            case 'picklist': $result = $this->StatisticReport->getPickList();
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        die;
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;

        $this->overrideTemplatePath();

        $page->addCrumb('StatisticReport', ['plugin' => 'StatisticReport', 'controller' => 'StatisticReport', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('StatisticReport'));

        $arra_temp = ['all'=>__('All')];
        
        if (in_array($action, ['add', 'edit','view'])) {
            $page->get('institution_types_id')->setControlType('hidden')->setOptions(
                $arra_temp + $this->StatisticReport->getInstitutionType(), false);

            $page->get('end_date')->setControlType('date');
            $page->get('ecp')->setControlType('hidden');

            $page->get('start_date')->setControlType('date');

            $page->get('academic_periods_id')->setControlType('hidden')->setOptions(
                $this->StatisticReport->getAcademicPeriod(), false);

            $page->get('region')->setControlType('hidden')->setOptions(
                $arra_temp + $this->StatisticReport->getRegions(), false);

            $page->get('template')->setControlType('select')->setOptions(
                [
                    $this->StatisticReport->getTemplates()
                ], false);

            $page->get('districts')->setControlType('hidden')->setOptions(
                $arra_temp + $this->StatisticReport->getDistricts(), false);

            $page->get('institutions_id')->setControlType('hidden')->setOptions(
                $arra_temp + $this->StatisticReport->getOrganizations(), false);

            $page->get('operator')->setControlType('select')->setOptions(
                $this->StatisticReport->getUserByRole(), false);

            $page->get('owner')->setControlType('select')->setOptions(
                $this->StatisticReport->getUserByRoles(), false);

        }


    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->initializeToolbars();
    }
    
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        parent::index();
        $this->Page->exclude(['params']);

        $this->paginate = [
            'contain' => ['ModifiedUsers', 'CreatedUsers']
        ];
        $faq = $this->paginate($this->StatisticReport);

        $this->set(compact('statistic_report'));
        $this->set('_serialize', ['statistic_report']);
    }

    private function initializeToolbars() {
        $request = $this->request;
        $currentAction = $request->action;

        $page = $this->Page;
        $data = $page->getData();

        $actions = $page->getActions();
        $disabledActions = [];
        foreach ($actions as $action => $value) {
            if ($value == false) {
                $disabledActions[] = $action;
            }
        }

        switch ($currentAction) {
            case 'view':
                $primaryKey = !is_array($data) ? $data->primaryKey : $data['primaryKey'];
                $page->addToolbar('export', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('Export'),
                        'url' => ['action' => 'export',
                                "institution_types_id"  =>  $data->institution_types_id,
                                "academic_periods_id"   =>  $data->academic_periods_id,
                                "region"                =>  $data->region,
                                "districts"             =>  $data->districts,
                                "institutions_id"       =>  $data->institutions_id,
                                "start_date"            =>  $data->start_date,
                                "end_date"              =>  $data->end_date,
                                "template"              =>  $data->template,
                                "name"                  =>  $data->name,
                            ],
                        'urlParams' => 'QUERY',
                        'iconClass' => 'fa kd-export',
                        'linkOptions' => ['title' => __('Export'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);
                break;
        }
    }

    public function export() {
        $request = $this->request;
        $query = $request->query;
        return $this->redirect(
            ['controller' => 'TemplateReport', 'action' => 'template', 'plugin' => 'TemplateReport', 'query'=> $query]
        );
    }


//    public function edit() {
//
//    }
    
}
