<?php
namespace StatisticReportHistory\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use StatisticReportHistory\Model\Entity\StatisticReportHistory;
use TemplateReport\Controller;


/**
 * Faq Controller
 *
 * @property \StatisticReportHistory\Model\Table\FaqTable $Faq
 */
class StatisticReportHistoryController extends PageController
{
    public $helpers = ['StatisticReportHistory.Signing'];

    public function initialize()
    {
        $this->attachAngularModules();
        $this->loadModel('StatisticReportHistory.Generated');
        $this->loadModel('StatisticReportHistory.StatisticReportHistory');
        $this->loadModel('StatisticReportHistory.StatisticReport');
        parent::initialize();
        $this->loadComponent('Security');
        $this->Page->enable(['download']);
        if ($this->Auth->user('id')) {
            $this->Auth->allow(['sign', 'quickCreate', 'createRecord']);
            $this->Security->config('unlockedActions', ['sign']);
        }
        $this->loadComponent('StatisticReportHistory.Sign');
    }

    private function attachAngularModules()
    {
        $action = $this->request->action;

        switch ($action) {
            case 'index':
                $this->set('angularModule', [
                    'controller' => 'GeneratedReport',
                    'path'       => 'StatisticReportHistory.angular/controller/GeneratedReport',
                ]);
                break;
        }
    }

    public function sign()
    {
        $this->Sign->sign();
    }

    function add() {
        parent::add();
    }

    public function statisticField()
    {
        $page = $this->Page;
        $arra_temp = ['all'=>__('All')];

        if($this->request->action != 'quickCreate' ) {
            $typeField = 'select';
            $_typeField = 'hidden';
        } else {
            $typeField = 'hidden';
            $_typeField = 'select';
        }

        $page->get('institution_types_id')->setControlType($typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getInstitutionType(), false);

        $page->get('end_date')->setControlType('date')->setValue('2020-09-01');

        $page->get('start_date')->setControlType('date')->setValue('2019-09-01');
        $page->get('code')->setControlType($typeField);
        $page->get('short_name')->setControlType($typeField);
        $page->get('name')->setControlType($typeField);

        $page->get('academic_periods_id')->setControlType($_typeField)->setOptions(
            $this->StatisticReport->getAcademicPeriod(), false)->setValue('30');

        $page->get('region')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getRegions(), false);

        $page->get('template')->setControlType($typeField)->setOptions( [
                $this->StatisticReport->getTemplates()
        ], false);

        $page->get('modified_user_id')->setControlType('hidden');
        $page->get('created_user_id')->setControlType('hidden');
        $page->get('ecp')->setControlType('hidden');

        $page->get('districts')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getDistricts(), false);

        $page->get('institutions_id')->setControlType($typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getOrganizations(), false);

        $page->get('operator')->setControlType('hidden')->setOptions(
            $this->StatisticReport->getUserByRole(), false);

        $page->get('owner')->setControlType('hidden')->setOptions(
            $this->StatisticReport->getUserByRoles(), false);
    }

    public function beforeFilter(Event $event)
    {
        if (in_array($this->request->action, ['createRecord'])) {
            $this->eventManager()->off($this->Csrf);
        }

        parent::beforeFilter($event);
        $action = $this->request->action;
        $page = $this->Page;

        $page->addCrumb('StatisticReportHistory', ['plugin' => 'StatisticReportHistory', 'controller' => 'StatisticReportHistory', 'action' => 'index']);
        if ($action) {
            $page->addCrumb($action);
        }

        $page->setHeader(__('StatisticReportHistory'));

        if($action === 'view') {
            $page->get('file_content_hash')
                ->setAttributes('fileNameField', 'file_name_hash');
        }

        if (in_array($action, ['add', 'edit'])) {
            $page->get('institution_types_id')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getInstitutionType(), false);

            $page->get('end_date')->setControlType('date');

            $page->get('start_date')->setControlType('date');

            $page->get('academic_periods_id')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getAcademicPeriod(), false);

            $page->get('region')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getRegions(), false);

            $page->get('districts')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getDistricts(), false);

            $page->get('institutions_id')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getOrganizations(), false);

            $page->get('operator')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getUserByRole(), false);

            $page->get('owner')->setControlType('select')->setOptions(
                $this->StatisticReportHistory->getUserByRoles(), false);

        }
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
        $this->Page->exclude(['content']);
        $this->Page->get('id')->setSortable(true);
        $this->paginate = [
            'contain' => ['ModifiedUsers', 'CreatedUsers']
        ];

        $this->Page->exclude(['linkreport','file_content','file_name','hash','template','ecp','operator','owner','region','institution_types_id','districts','name','code','org_name',]);

        $this->set(compact('statistic_report_history'));
        $this->set('_serialize', ['statistic_report_history']);
        $this->render('Page/index');
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->initializeToolbars();
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

        switch ( $currentAction ) {
            case 'index' :
                $page->addToolbar('generated_export', [
                    'type'      => 'element',
                    'element'   => 'StatisticReportHistory.select',
                    'data' => [
                        'title' => __('Export'),
                        'url' => ['action' => false],
                        'iconClass' => 'fa kd-export',
                        'linkOptions' => [
                            'title'             =>  __('Export'),
                            'id'                =>  'generated-report',
                            'name'              =>  'generated_report',
                            'options'           =>  $this->StatisticReport->getStatistics(),
                            'action_event'      =>  'generate-report-event'
                        ]
                    ],
                    'options' => []
                ]);
                break;
        }
    }

    function quickCreate($id = false) {
        $name = $this->StatisticReport->getStatisticReportName(['id'=>$id]);
        $this->set('short_name',$name);
        $this->set('modalTitle',"<b>".__('Generated Statistic')."</b> : <br /><span style='box-shadow: 0px 2px 9px rgba(0, 0, 0, 0.25)'>{$name}</span>"  );
        $this->Page->loadElementsFromTable($this->StatisticReport);
        $this->statisticField();

        $this->edit_($id);
        $this->render('Page/quick_create','modal');
    }

    function createRecord() {
        $StatisticReportHistory = $this->StatisticReportHistory->newEntity();
        $page = $this->Page;
        $data = $this->request->data;
        $unsets = ['period','template_id'];
        $resData = [];

        foreach ( $data as $key => $item ) {
            $nameField = str_replace(']','',str_replace('StatisticReport[','',$item['name']));
            if( in_array($nameField, $unsets) ) {
                if( $nameField == 'template_id' ) {
                    $templateId = $data[$key]['value'];
                }
                unset($data[$key]);
                continue;
            }

            $StatisticReportHistory->$nameField = $data[$key]['value'];
            $resData[$nameField] = $data[$key]['value'];
            $data['StatisticReportHistory'][$nameField] = $data[$key]['value'];
        }

        $data['StatisticReportHistory']['submit'] = 'save';

        $templateCode = $this->StatisticReportHistory->getTemplateCode(['id'=>$templateId]);
        $path = ROOT."/webroot/export/temp/";
        $filesList = [
            'osh_1' => 'xlsx',
            'osh_1_15' => 'xlsx',
            'tab85k' => 'XLS',
            'tabl' => 'XLS',
            'fnk2' => 'xlsx'
        ];
        $templateFile = null;
        if(array_key_exists($templateCode, $filesList)) {
            $templateFile = file_get_contents($path . $templateCode . '.' . $filesList[$templateCode]);
        }

        $table = $page->getMainTable();
        $queryOptions = $page->getQueryOptions();
        $patchOption = $queryOptions['user'];
        $entity = $table->newEntity($data, $patchOption);
        $entity->file_name = $templateCode . '_' . time() . $filesList[$templateCode];
        $entity->file_content = $templateFile;
        $table->save($entity, $patchOption);

        echo json_encode(['template' => $templateCode]);
        die;

    }

    public function edit_($id)
    {
        $page = $this->Page;
        $request = $this->request;

        if ($page->hasMainTable()) {
//            $primaryKeyValue = $page->decode($id);
            $primaryKeyValue = $id;
            $table = $page->getMainTable();
            $pageStatus = $page->getStatus();
            $response = null;
            $entity = null;

            if ($table->exists($primaryKeyValue)) {
                // autoContain and findEdit needs to be executed on POST/PUT/PATCH
                // so that on validation error, correct values will be displayed
                $page->autoContains($table);
                $queryOptions = $page->getQueryOptions();

                if ($table->hasFinder('Edit')) {
                    $queryOptions->offsetSet('finder', 'Edit');
                }
                $entity = $table->get($primaryKeyValue, $queryOptions->getArrayCopy());
                $page->attachPrimaryKey($table, $entity);

                if ($request->is(['post', 'put', 'patch'])) {
                    try {
                        $patchOption = ['user' => $queryOptions['user']];
                        $entity = $table->patchEntity($entity, $request->data, $patchOption);
                        $result = $table->save($entity, $patchOption);

                        if ($result) {
                            $pageStatus->setMessage('The record has been updated successfully.');
                            $page->setAlert($pageStatus->getMessage());
                            die('updated');
                            $response = $page->redirect(['action' => 'view']);
                        } else {
                            Log::write('debug', $entity->errors());
                            if ($entity->errors()) {
                                $page->setVar('error', $entity->errors());
                            }

                            $pageStatus->setCode(PageStatus::VALIDATION_ERROR)
                                ->setType('error')
                                ->setMessage('The record is not updated due to errors encountered.');

                            $page->setAlert($pageStatus->getMessage(), 'error');
                        }
                    } catch (Exception $ex) { // should catch more specific exceptions to handle the exception appropriately
                        Log::write('error', $ex);
                        $msg = $ex->getMessage();
                        $pageStatus->setCode(PageStatus::UNEXPECTED_ERROR)
                            ->setType('error')
                            ->setError(true)
                            ->setMessage($msg);

                        $page->setAlert($pageStatus->getMessage(), 'error');
                    }

                    $errors = $entity->errors();
                    $page->setVar('errors', $errors);
                }
                $page->setVar('data', $entity);
            } else { // if primary key does not exists
                $pageStatus->setCode(PageStatus::RECORD_NOT_FOUND)
                    ->setType('warning')
                    ->setError(true)
                    ->setMessage('The record does not exists.');

                $page->setAlert($pageStatus->getMessage(), 'warning');
                $response = $page->redirect(['action' => 'view']);
            }
            if (!is_null($response)) {
                return $response;
            }
        }
    }
}
