<?php
namespace TemplateReport\Controller;

use App\Controller\PageController;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use StatisticReport\Model\Table;


use DateTime;
use DateInterval;
//use TemplateReport\Excels\F2NK;

use TemplateReport\Controller\Excels\Component;

/**
 * Faq Controller
 *
 * @property \TemplateReport\Model\Table\FaqTable $Faq
 */
class TemplateReportController extends PageController
{
    private $recordId = false;

    public $uses = array(
        "TemplateReport",
        "GeneratedTemplate",
        "StatisticReport"
    );

    public function initialize()
    {
        $this->loadModel('TemplateReport.StatisticReport');
        $this->loadModel('TemplateReport.Generated');
        $this->loadModel('TemplateReport.TemplateReport');

        parent::initialize();
        $this->loadComponent('Security');
        $this->Auth->allow(['osh_1', 'osh_1_15', 'tabl', 'tab85k', 'fnk2']);
        $this->loadComponent('TemplateReport.Template');
        $this->attachAngularModules();
    }

    private function attachAngularModules()
    {
        $action = $this->request->action;

        switch ($action) {
            case 'generated_index':
//                $this->set('ngController', 'GeneratedReport');
                $this->set('angularModule', [
                    'controller' => 'GeneratedReport',
                    'path'       => 'TemplateReport.angular/controller/GeneratedReport',

            ]);break;
        }
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->initializeToolbars();
        $this->Security->config('unlockedActions', ['createRecord','quickCreate',]);

        $action = $this->request->action;
        $page = $this->Page;

        if(preg_match('/generated/',$action)){
            $this->setName('Generated','generated_index');
        } else if(preg_match('/statistic/',$action)){
            $this->setName('StatisticReport','statistic_index');
        } else {
            $this->setName();
        }


        if (in_array($action, ['add', 'edit'])) {
            $page->get('type')->setControlType('select')->setOptions(
                [
                    'statistic'     => __('statistic'),
                    'order'         => __('order'),
                    'ref'           => __('ref'),
                ], false);
        }
    }

    function setName( $header='TemplateReport', $action='index', $plugin='TemplateReport', $controller='TemplateReport') {
        $page = $this->Page;
        $page->addCrumb($header, [ 'plugin' => $plugin, 'controller' => $controller, 'action' => $action ]);
        $page->setHeader(__($header));
    }

    public function statisticField()
    {
        $page = $this->Page;
        $arra_temp = ['all'=>__('All')];

        if($this->request->action == 'statistic_add' ||$this->request->action == 'statistic_edit' ) {
            $typeField = 'select';
            $_typeField = 'hidden';
        } else {
            $typeField = 'hidden';
            $_typeField = 'select';
        }

        $page->get('institution_types_id')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getInstitutionType(), false);

        $page->get('end_date')->setControlType('date');

        $page->get('start_date')->setControlType('date');

        $page->get('academic_periods_id')->setControlType($_typeField)->setOptions(
            $this->StatisticReport->getAcademicPeriod(), false);

        $page->get('region')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getRegions(), false);

        $page->get('template')->setControlType($typeField)->setOptions(
            [
                $this->StatisticReport->getTemplates()
            ], false);
        $page->get('modified_user_id')->setControlType('hidden');
        $page->get('created_user_id')->setControlType('hidden');
        $page->get('ecp')->setControlType('hidden');

        $page->get('districts')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getDistricts(), false);

        $page->get('institutions_id')->setControlType($_typeField)->setOptions(
            $arra_temp + $this->StatisticReport->getOrganizations(), false);

        $page->get('operator')->setControlType('hidden')->setOptions(
            $this->StatisticReport->getUserByRole(), false);

        $page->get('owner')->setControlType('select')->setOptions(
            $this->StatisticReport->getUserByRoles(), false);
    }

    public function generatedFieldset() {
        $page = $this->Page;
//        $page->get('institution_types_id')->setControlType('select')->setOptions(
//            $this->StatisticReportHistory->getInstitutionType(), false);
//
//        $page->get('end_date')->setControlType('date');
//
//        $page->get('start_date')->setControlType('date');
//
//        $page->get('academic_periods_id')->setControlType('select')->setOptions(
//            $this->StatisticReportHistory->getAcademicPeriod(), false);
//
//        $page->get('region')->setControlType('select')->setOptions(
//            $this->StatisticReportHistory->getRegions(), false);
//
//        $page->get('template')->setControlType('select')->setOptions(
//            [
//
//            ], false);
//
//        $page->get('districts')->setControlType('select')->setOptions(
//            $this->StatisticReportHistory->getDistricts(), false);
//
//        $page->get('institutions_id')->setControlType('select')->setOptions(
//            $this->StatisticReportHistory->getOrganizations(), false);
//
        $page->get('employeer')->setControlType('select')->setOptions (
            $this->Generated->getUserByRole(), false);
//
        $page->get('owner')->setControlType('select')->setOptions(
            $this->Generated->getUserByRoles(), false);
    }

    public function add() {
        parent::add();
        $this->render('TemplateReport.edit');
    }

    public function edit($id) {
        $this->recordId = $id;

        parent::edit($id);
        $this->render('TemplateReport.edit');
    }

    public function view($id) {
        $this->recordId = $id;
        parent::view($id);
        $this->render('TemplateReport.view');
    }

    public function getEntity($id)
    {
        $page = $this->Page;
        $request = $this->request;

        if ($request->is(['get', 'ajax']) && $page->hasMainTable()) {
            $primaryKeyValue = $page->decode($id);
            $table = $page->getMainTable();
            $primaryKey = $table->primaryKey();
            if (!is_array($primaryKey)) { // if primary key is not composite key, then hide from index page
                $page->exclude($primaryKey);
            }

            if ($table->exists($primaryKeyValue)) {
                $page->autoContains($table);
                $queryOptions = $page->getQueryOptions();

                if ($table->hasFinder('View')) {
                    $queryOptions->offsetSet('finder', 'View');
                }

                $entity = $table->get($primaryKeyValue, $queryOptions->getArrayCopy());
                return $entity;
            }
        }
    }

    private function initializeToolbars() {
        $request = $this->request;
        $currentAction = $request->action;
        $page = $this->Page;
        $data = $page->getData();

        $actions = $page->getActions();
//        var_dump($actions);
        $disabledActions = [];
        foreach ($actions as $action => $value) {
            if ($value == false) {
                $disabledActions[] = $action;
            }
        }

        switch ($currentAction) {
            case 'view':
                $page->addToolbar('export', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('Export'),
                        'url' => ['action' => 'pdfView',
                            "template_id"  =>  $this->getEntity($request->pass[0])->toArray()['id'],
                            "plugin_name"       =>  'TemplateReport',
                            "record"       =>  $this->getEntity($request->pass[0])->toArray()['id'],
                        ],
                        'urlParams' => 'QUERY',
                        'iconClass' => 'fa kd-export',
                        'linkOptions' => ['title' => __('Export'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);
                break;
            case 'statistic_index' :
                $page->addCrumb('StatisticReport', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'statistic_index']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('statistic_add', $disabledActions)) {
                    $page->addToolbar('statistic_add', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Add'),
                            'url' => ['action' => 'statistic_add'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => []
                    ]);
                }
                if (!in_array('search', $disabledActions)) {
                    $page->addToolbar('search', [
                        'type' => 'element',
                        'element' => 'Page.search',
                        'data' => [],
                        'options' => []
                    ]);
                };
                break;
            case 'statistic_view' :
                $page->addCrumb('StatisticReport', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'statistic_index']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('statistic_add', $disabledActions)) {
                    $page->addToolbar('statistic_add', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Add'),
                            'url' => ['action' => 'statistic_add'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('statistic_edit', $disabledActions)) {
                    $page->addToolbar('statistic_edit', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Edit'),
                            'url' => ['action' => 'statistic_edit'],
                            'iconClass' => 'fa kd-edit',
                            'linkOptions' => ['title' => __('Edit')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('statistic_delete', $disabledActions)) {
                    $page->addToolbar('statistic_delete', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Delete'),
                            'url' => ['action' => 'statistic_delete'],
                            'iconClass' => 'fa kd-trash',
                            'linkOptions' => ['title' => __('Delete')]
                        ],
                        'options' => []
                    ]);
                }
                break;
            case 'statistic_edit' :
                $page->addCrumb('StatisticReport', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'statistic_index']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('statistic_index', $disabledActions)) {
                    $page->addToolbar('statistic_index', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Index'),
                            'url' => ['action' => 'statistic_index'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('statistic_delete', $disabledActions)) {
                    $page->addToolbar('statistic_delete', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Delete'),
                            'url' => ['action' => 'statistic_delete'],
                            'iconClass' => 'fa kd-trash',
                            'linkOptions' => ['title' => __('Delete')]
                        ],
                        'options' => []
                    ]);
                }


                if (!in_array('statistic_view', $disabledActions)) {
                    $page->addToolbar('statistic_view', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('View'),
                            'url' => ['action' => 'statistic_view'],
                            'iconClass' => 'fa kd-back',
                            'linkOptions' => ['title' => __('View')]
                        ],
                        'options' => []
                    ]);
                }
                break;
            case 'generated_index' :
                $page->addCrumb('Generated', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'generated_index']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('generated_add', $disabledActions)) {
                    $page->addToolbar('generated_add', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Add'),
                            'url' => ['action' => 'generated_add'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => ['id'=>'generated_report']
                    ]);
                }
                if (!in_array('search', $disabledActions)) {
                    $page->addToolbar('search', [
                        'type' => 'element',
                        'element' => 'Page.search',
                        'data' => [],
                        'options' => []
                    ]);
                };

                if (!in_array('generated_export', $disabledActions)) {
                    $page->addToolbar('generated_export', [
                        'type'      => 'element',
                        'element'   => 'TemplateReport.select',
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
                }
                break;
            case 'generated_view' :
                $page->addCrumb('Generated', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'statistic_index']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('generated_add', $disabledActions)) {
                    $page->addToolbar('generated_add', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Add'),
                            'url' => ['action' => 'generated_add'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('generated_edit', $disabledActions)) {
                    $page->addToolbar('generated_edit', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Edit'),
                            'url' => ['action' => 'generated_edit'],
                            'iconClass' => 'fa kd-edit',
                            'linkOptions' => ['title' => __('Edit')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('generated_delete', $disabledActions)) {
                    $page->addToolbar('generated_delete', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Delete'),
                            'url' => ['action' => 'generated_delete'],
                            'iconClass' => 'fa kd-trash',
                            'linkOptions' => ['title' => __('Delete')]
                        ],
                        'options' => []
                    ]);
                }
                break;
            case 'generated_edit' :
                $page->addCrumb('Generated', ['plugin' => 'TemplateReport', 'controller' => 'TemplateReport', 'action' => 'generated_edit']);
                if ($action) {
                    $page->addCrumb($action);
                }
                if (!in_array('generated_add', $disabledActions)) {
                    $page->addToolbar('generated_add', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Index'),
                            'url' => ['action' => 'generated_add'],
                            'iconClass' => 'fa kd-add',
                            'linkOptions' => ['title' => __('Add')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('generated_delete', $disabledActions)) {
                    $page->addToolbar('generated_delete', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('Delete'),
                            'url' => ['action' => 'generated_delete'],
                            'iconClass' => 'fa kd-trash',
                            'linkOptions' => ['title' => __('Delete')]
                        ],
                        'options' => []
                    ]);
                }


                if (!in_array('generated_view', $disabledActions)) {
                    $page->addToolbar('generated_view', [
                        'type'      => 'element',
                        'element'   => 'Page.button',
                        'data' => [
                            'title' => __('View'),
                            'url' => ['action' => 'generated_view'],
                            'iconClass' => 'fa kd-back',
                            'linkOptions' => ['title' => __('View')]
                        ],
                        'options' => []
                    ]);
                }
                break;
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
        $this->Page->exclude(['content']);
        $this->paginate = [
            'contain' => ['ModifiedUsers', 'CreatedUsers']
        ];

        $this->set(compact('template_report'));
        $this->set('_serialize', ['template_report']);

        $this->render('Page/index');
    }

    function quickCreate($id) {
        $this->Page->loadElementsFromTable($this->StatisticReport);
        $this->statisticField();
        $name = $this->StatisticReport->getStatisticReportName(['id'=>$id]);
        $this->set('modalTitle',"<b>".__('Generated Statistic')."</b> : <br /><span style='box-shadow: 0px 2px 9px rgba(0, 0, 0, 0.25)'>{$name}</span>"  );
        $this->edit_($id);
        $this->render('Page/quick_create','modal');
    }

    function createRecord() {
        $this->Page->loadElementsFromTable($this->StatisticReport);
        $page = $this->Page;
        $table = $page->getMainTable();
        $queryOptions = $page->getQueryOptions();
        $patchOption = $queryOptions['user'];
        $request = $this->request;
        var_dump($request->data);
        $entity = $table->newEntity($request->data, $patchOption);
        $result = $table->save($entity, $patchOption);
        die('here');
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

    function overloadAction($prefix = '') {
        $action = $this->request->action;

        $data = $this->Page->getData();

        foreach ($this->Page->getData() as $entity){
            $rowActionsArray = $this->rewriteAction($this->Page->getRowActions($entity),$prefix);

//            var_dump($rowActionsArray);
            if ($entity instanceof Entity) {
                $entity->rowActions = $rowActionsArray;
            } else {
                $entity['rowActions'] = $rowActionsArray;
            }
        }
        $this->Page->setVar($data,'data');
    }

    function beforeRender(Event $event) {
        parent::beforeRender($event);
    }

    function rewriteAction($rowActionsArray,$prefix = '') {
        foreach ($rowActionsArray as $action => $item){
            $rowActionsArray[$action]['url']['action'] = $prefix.$rowActionsArray[$action]['url']['action'];
        }
        return $rowActionsArray;
    }

    public function pdfView($template_id = null) {
        $query = $this->request->query;
        $content = $this->TemplateReport->getTemplate($query['template_id']);
        $row = $this->TemplateReport->getRecord($query['plugin_name'],$query['record']);
        $content = $this->parsingTemplate($content,$row);
        $this->set('template',$content);
        $this->viewBuilder()->layout('ajax');
        $this->set('title', 'My Great Title');
        $this->set('file_name', 'Refer' . '.pdf');
        $this->response->type('pdf');
    }

    public function excelGenerated( ) {
        $query = $this->request->query;
        $row = $this->TemplateReport->getTemplate($query['template_id'],['content','name']);
        $table = $row['content'];

        if(isset($query['filename'])) {
            $filename = $query['filename'].".xlsx";
        } else {
            $filename = $row['name'].".xlsx";
        }

        $tmpfile = tempnam(sys_get_temp_dir(), 'html');
        file_put_contents($tmpfile, $table);
        $objPHPExcel     = new \PHPExcel();
        $excelHTMLReader = \PHPExcel_IOFactory::createReader('HTML');
        $excelHTMLReader->loadIntoExisting($tmpfile, $objPHPExcel);
        $objPHPExcel->getActiveSheet()->setTitle('any name you want');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // header for .xlxs file
        header('Content-Disposition: attachment;filename='.$filename); // specify the download file name
        header('Cache-Control: max-age=0');

        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $writer->save('php://output');
        die;
    }

    public function generatedTemplateOsh()
    {
        $request = $this->request;
        $query = $request->query;
        $AbstractF2NK = new F2NK($this->request,$this->TemplateReport);
        $f2nk = new Osh($this->TemplateReport,$query);
        $AbstractF2NK->addExcel($f2nk);
        $AbstractF2NK->buildExcel();
        $fileName = $query['query']['name'].'_'.date('Y-m-d',time()).'.xls';
        $AbstractF2NK->download($fileName);
        die;
    }

    public function osh_1() {
        $path = ROOT."/webroot/export/temp/osh_1.xlsx";
        $file = file_get_contents($path);
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=osh_1.xlsx");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        echo $file;
        exit();
    }

    public function osh_1_15() {
        $path = ROOT."/webroot/export/temp/osh_1_15.xlsx";
        $file = file_get_contents($path);
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=osh_1_15.xlsx");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        echo $file;
        exit();
    }

    public function tab85k() {
        $path = ROOT."/webroot/export/temp/tab85k.XLS";
        $file = file_get_contents($path);
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=tab85k.XLS");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        echo $file;
        exit();
    }

    public function tabl() {
        $path = ROOT."/webroot/export/temp/tabl.XLS";
        $file = file_get_contents($path);
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=tabl.XLS");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        echo $file;
        exit();
    }

    public function fnk2() {
        $path = ROOT."/webroot/export/temp/fnk2.xlsx";
        $file = file_get_contents($path);
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=fnk2.xlsx");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        echo $file;
        exit();
    }

    public function generatedTemplateTab85k()
    {
        $request = $this->request;
        $query = $request->query;
        $AbstractF2NK = new F2NK($this->request,$this->TemplateReport);
        $f2nk = new Tab85k($this->TemplateReport,$query);
        $AbstractF2NK->addExcel($f2nk);
        $AbstractF2NK->buildExcel();
        $fileName = $query['query']['name'].'_'.date('Y-m-d',time()).'.xls';
        $AbstractF2NK->download($fileName);
    }

    public function generatedTemplateTabL()
    {
        $request = $this->request;
        $query = $request->query;
        $AbstractF2NK = new F2NK($this->request,$this->TemplateReport);
        $f2nk = new TabL($this->TemplateReport,$query);
        $AbstractF2NK->addExcel($f2nk);
        $AbstractF2NK->buildExcel();
        $fileName = $query['query']['name'].'_'.date('Y-m-d',time()).'.xls';
        $AbstractF2NK->download($fileName);
    }

    private function parsingTemplate($content,$row) {
        foreach ( $row as $fieldName => $fieldValue ) {
            $content = str_replace('$__'.$fieldName.'__',trim($fieldValue),$content);
        }
        return $content;
    }

    public function template() {
        $request = $this->request;
        $query = $request->query;
        $AbstractF2NK = new F2NKComponent($this->request,$this->TemplateReport);
//        $f2nk = new F2NK1($this->TemplateReport,$query);
//        $AbstractF2NK->addExcel($f2nk);
//        $AbstractF2NK->buildExcel();
//        $fileName = $query['query']['name'].'_'.date('Y-m-d',time()).'.xls';
//        $AbstractF2NK->download($fileName);
        die();
    }
}


class F2NK {

    protected $templateClasses = [];
    protected $rootFolder = 'import';
    protected $positionStart = 'A1';
    protected $positionX = 1;
    protected $positionY = 1;
    protected $sheet;
    protected $PHPExcel;
    protected $request;
    protected $templateReportTable;


    function __construct($request,$templateReportTable)
    {
        $PHPExcel = new \PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $this->request              = $request;
        $this->templateReportTable  = $templateReportTable;
        $this->PHPExcel             = $PHPExcel;
        $this->sheet                = $PHPExcel->getActiveSheet();
    }

    function addExcel(F2NK $templateClass){
        $this->templateClasses[] = $templateClass;
    }

//    abstract protected function createExcelPart();
    function buildExcel() {
        if(empty($this->templateClasses)) {
            throw new \Exception('Not added to worksheet part elements');
        }

        foreach ($this->templateClasses as $templateClass) {
            $templateClass->createExcelPart($this->sheet);
            $this->prepareDownload();
        }
    }

    protected function prepareDownload()
    {
        $folder = WWW_ROOT . $this->rootFolder;

//        foreach ($this->PHPExcel->getWorksheetIterator() as $worksheet) {
//            foreach ($worksheet->getColumnIterator() as $column) {
//                $worksheet
//                    ->getColumnDimension($column->getColumnIndex())
//                    ->setAutoSize(true);
//            }
//        }
        if (!file_exists($folder)) {
            umask(0);
            mkdir($folder, 0777);
        } else {
            $fileList = array_diff(scandir($folder), array('..', '.'));
            $now = new DateTime();
            // delete all old files that are more than one hour old
            $now->sub(new DateInterval('PT1H'));

            foreach ($fileList as $file) {
                $path = $folder . DS . $file;
                $timestamp = filectime($path);
                $date = new DateTime();
                $date->setTimestamp($timestamp);

                if ($now > $date) {
                    if (!unlink($path)) {
                        $this->_table->log('Unable to delete ' . $path, 'export');
                    }
                }
            }
        }

        return $folder;
    }

    function download($excelFile) {
        $folder     = WWW_ROOT . $this->rootFolder;
        $excelPath  = $folder . DS . $excelFile;
        $filename   = basename($excelPath);

        $objWriter = new \PHPExcel_Writer_Excel2007($this->PHPExcel);
        $objWriter->save($excelPath);

        header("Pragma: public", true);
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($excelPath));
        echo file_get_contents($excelPath);
    }
}


class F2NK1 extends F2NK {

    private $connection;
    private $params;

    public function __construct($connection, $params)
    {

        $this->connection   = $connection;
        $this->params       = $params;
    }

    protected $rowHeaderTitles = [
        '3. Студенттердин жашы боюнча санынын болунушу (отчеттук-ж. 01.01. толук жашы жетилгендердин саны), адам',
        '3. Распределение численности студентов по возрасту (число полных лет на 01.01. отчетного года), человек'
    ];

    protected $rowHeaderTable = [
        'Саптын коду Код строки', '14-жаш жана кичуу 14 лет и менее', '15 жаш 15 лет', '16 жаш 16 лет', '17 жаш 17 лет', '18 жаш 18 лет', '19 жаш 19 лет', '20 жаш 20 лет', '21 жаш 21 лет', '22 жаш 22 лет', '23 жаш 23 лет', '24 жаш 24 лет', '25-35 жаш 25-35 лет', '36 жаш жана улуу 36 лет и старше', 'Жыйынтыгы Итого',
    ];

    protected $rowSubHeaderTable = [
         'А', 'Б', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14',
    ];

    protected $leftRow = [
        'Жашы боюнча студенттердин саны Численность студентов в возрасте',
        'алардын ичинен аялдар из них: женщины',
    ];

    protected $leftRowCode = [
        '01',
        '02',
    ];

    protected $rowData = ['14','15','16','17','18','19','20','21','22','23','24','25','25_35','25_more',];


    protected function createExcelPart($sheet) {
//        add header title
        $this->positionX = 0;
        foreach ($this->rowHeaderTitles as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            ++$this->positionY;
        }
        $this->positionY++;

//        add header table
        $this->positionX = 1;
        foreach ($this->rowHeaderTable as $value) {

            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add sub header table
        $this->positionX = 1;
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;


//        add count all value
        $this->positionX = 0;
        foreach ($this->leftRow as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;

//        add code all value
        $this->positionX = 1;
        foreach ($this->leftRowCode as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;



        $counts = $this->connection->getCountStudentByGender($this->params);

        $this->positionX = 2;
        $total = 0;

        foreach ($this->rowData as $item) {
            if(isset($counts['total'][$item])){
                $value = $counts['total'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
        $this->positionY++;

//        add count by female
        $this->positionX = 2;
        $total = 0;
        foreach ($this->rowData as $item) {
            if(isset($counts['female'][$item])){
                $value = $counts['female'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
    }
}

class Osh extends F2NK {
    private $connection;
    private $params;

    private $header_1 = '1. ОКУУЧУЛАРДЫ ОКУТУУ ТИЛИ ЖАНА КЛАССТАР БОЮНЧА БЈЛ²ШТ²Р²² (адам)';
    private $header_2 = '1. РАСПРЕДЕЛЕНИЕ УЧАЩИХСЯ ПО ЯЗЫКАМ ОБУЧЕНИЯ И ПО КЛАССАМ (человек)';

    private $header_sub_1 = 'Ар бир тил боюнча окутулган балдардын саны ¼з³нч¼ к¼рс¼т³лс³н';
    private $header_sub_2 = 'Указать отдельно число учащихся, обучающихся на каждом языке';

    private $header_sub__1 = '  (чет тилдери иштеп чыгууга катышпайт)';
    private $header_sub__2 = ' (иностранные языки в разработке не участвуют)';

    public function __construct($connection, $params)
    {

        $this->connection   = $connection;
        $this->params       = $params;
    }

    protected function createExcelPart($sheet) {

        $styleBorder = array(
            'borders' => array(
                'outline' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '00000000'),
                ),
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        $styleCenterH = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        $styleCenterV = array(
            'alignment' => array(
                'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
//die;
        $sheet->mergeCells("A1:J1");
        $sheet->getStyle("A1:J1")->applyFromArray($styleBorder);
        $sheet->mergeCells("K1:T1");
        $sheet->getStyle("K1:T1")->applyFromArray($styleBorder);

        $sheet->setCellValue("A1", $this->header_1);
        $sheet->setCellValue("K1", $this->header_2);

        $sheet->mergeCells("A2:J2");
        $sheet->mergeCells("K2:T2");
        $sheet->setCellValue("A2", $this->header_sub_1);
        $sheet->getStyle("A2", $styleCenterH);
        $sheet->setCellValue("K2", $this->header_sub_2);
        $sheet->getStyle("K2", $styleCenterH);

        $sheet->mergeCells("A3:J3");
        $sheet->mergeCells("K3:T3");
        $sheet->setCellValue("A3", $this->header_sub__1);
        $sheet->getStyle("A3", $styleCenterH);
        $sheet->setCellValue("K3", $this->header_sub__2);
        $sheet->getStyle("K3", $styleCenterH);

        $sheet->mergeCells("A4:A7");
        $sheet->getStyle("A4:A7")->applyFromArray($styleBorder);
        $sheet->getStyle("A4")->applyFromArray($styleCenterV);
        $sheet->setCellValue("A4","Окутуунун\nтили");

        $sheet->setCellValue("B4","Сап тын");
        $sheet->setCellValue("B5","коду");
        $sheet->setCellValue("B7","Код строки");
        $sheet->getStyle("B4:B7")->applyFromArray($styleBorder);

        $sheet->mergeCells("C4:J4");
        $sheet->mergeCells("C5:J5");
        $sheet->getStyle("C4:J5")->applyFromArray($styleBorder);
        $sheet->setCellValue("C4","Класстар");

        $sheet->mergeCells("K4:Q4");
        $sheet->mergeCells("K5:Q5");
        $sheet->getStyle("K4:Q5")->applyFromArray($styleBorder);
        $sheet->setCellValue("K4","Классы");

        $sheet->mergeCells("R4:S4");

        $sheet->setCellValue("R4",'Бардыгы');

        $sheet->mergeCells("R5:S5");
        $sheet->setCellValue("R5","(1-12-граф. cуммасы)");

        $sheet->mergeCells("R6:S6");
        $sheet->setCellValue("R6",'Всего (сумма)');

        $sheet->mergeCells("R7:S7");
        $sheet->setCellValue("R7","(граф 1-12)");

        $sheet->mergeCells("R8:S8");
        $sheet->setCellValue("R8","13");
        $sheet->getStyle("R8")->applyFromArray($styleBorder);
        $sheet->getStyle("R4:S7")->applyFromArray($styleBorder);

        $sheet->mergeCells("T4:T7");
        $sheet->getStyle("T4:T7")->applyFromArray($styleBorder);
        $sheet->getStyle("A4")->applyFromArray($styleCenterH);
        $sheet->setCellValue("T4","Язык обучения");
        $sheet->getStyle("T4")->applyFromArray($styleBorder);


        $sheet->mergeCells("C6:E6");
        $sheet->getStyle("C6:E6")->applyFromArray($styleCenterH);
        $sheet->setCellValue("C6","Даярдоочу/мектепке чейинки даярдоо");

        $sheet->mergeCells("C7:E7");
        $sheet->getStyle("C7:E7")->applyFromArray($styleCenterH);
        $sheet->setCellValue("C7","Подготовительный/предшкольная подготовка");
        $sheet->getStyle("C6:E8")->applyFromArray($styleBorder);

//        start row
        $sheet->getStyle("A8")->applyFromArray($styleBorder);
        $sheet->setCellValue("A8","A");

        $sheet->getStyle("B8")->applyFromArray($styleBorder);
        $sheet->setCellValue("B8","B");

        $sheet->mergeCells("C8:E8");
        $sheet->getStyle("C8:E8")->applyFromArray($styleBorder);
        $sheet->setCellValue("C8","1");

        $sheet->getStyle("F6:F7")->applyFromArray($styleBorder);
        $sheet->setCellValue("F7","1");
        $sheet->getStyle("F8")->applyFromArray($styleBorder);
        $sheet->setCellValue("F8","2");

        $sheet->getStyle("G6:G7")->applyFromArray($styleBorder);
        $sheet->setCellValue("G7","2");
        $sheet->getStyle("G8")->applyFromArray($styleBorder);
        $sheet->setCellValue("G8","3");

        $sheet->getStyle("H6:H7")->applyFromArray($styleBorder);
        $sheet->setCellValue("H7","3");
        $sheet->getStyle("H8")->applyFromArray($styleBorder);
        $sheet->setCellValue("H8","4");

        $sheet->getStyle("I6:I7")->applyFromArray($styleBorder);
        $sheet->setCellValue("I7","4");
        $sheet->getStyle("I8")->applyFromArray($styleBorder);
        $sheet->setCellValue("I8","5");

        $sheet->mergeCells("J6:K6");
        $sheet->mergeCells("J8:K8");
        $sheet->mergeCells("J7:K7");
        $sheet->getStyle("J6:K7")->applyFromArray($styleBorder);
        $sheet->setCellValue("J7","5");
        $sheet->getStyle("J8:K8")->applyFromArray($styleBorder);
        $sheet->setCellValue("J8","6");

        $sheet->getStyle("L6:L7")->applyFromArray($styleBorder);
        $sheet->setCellValue("L7","6");
        $sheet->getStyle("L8")->applyFromArray($styleBorder);
        $sheet->setCellValue("L8","7");

        $sheet->getStyle("M6:M7")->applyFromArray($styleBorder);
        $sheet->setCellValue("M7","7");
        $sheet->getStyle("M8")->applyFromArray($styleBorder);
        $sheet->setCellValue("M8","8");

        $sheet->getStyle("N6:N7")->applyFromArray($styleBorder);
        $sheet->setCellValue("N7","8");
        $sheet->getStyle("N8")->applyFromArray($styleBorder);
        $sheet->setCellValue("N8","9");

        $sheet->getStyle("O6:O7")->applyFromArray($styleBorder);
        $sheet->setCellValue("O7","9");
        $sheet->getStyle("O8")->applyFromArray($styleBorder);
        $sheet->setCellValue("O8","10");

        $sheet->getStyle("P6:P7")->applyFromArray($styleBorder);
        $sheet->setCellValue("P7","10");
        $sheet->getStyle("P8")->applyFromArray($styleBorder);
        $sheet->setCellValue("P8","10");

        $sheet->getStyle("Q6:Q7")->applyFromArray($styleBorder);
        $sheet->setCellValue("Q7","11");
        $sheet->getStyle("Q8")->applyFromArray($styleBorder);
        $sheet->setCellValue("Q8","12");

        $sheet->getStyle("T8")->applyFromArray($styleBorder);
        $sheet->setCellValue("T8","A");
//        end row

        $firth = ['','','','','','','','',
            'Кыргыз: класстардын саны','','Орус:  класстардын саны','Окуучулардын саны','Узбек:  класстардын саны','окуучулардын саны','Таджик:  класстардын саны','окуучулардын саны','Уз алдынча сабак катары окутулган','башка эне тили:','Дунган','Уйгур','Немец','','','',
        ];
        $last  = ['','','','','','','','',
            'Кыргызский: число классов','численность учащихся','Русский: число классов','численность учащихся','Узбекский: число классов','численность учащихся','Таджикский: число классов','численность учащихся','Другой родной  язык, изучаемый  как самостоятельный предмет:','Дунганский','Уйгурский','Немецкий','','','','',
        ];


        for( $i=9; $i<=24; $i++ ) {
            $sheet->getStyle("A{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("A{$i}",$firth[$i]);

            $sheet->getStyle("B{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("B{$i}",$i-8);

            $sheet->mergeCells("C{$i}:E{$i}");
            $sheet->getStyle("C{$i}:E{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("C{$i}","1");

            $sheet->getStyle("F{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("F{$i}","2");

            $sheet->getStyle("G{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("G{$i}","3");

            $sheet->getStyle("H{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("H{$i}","4");

            $sheet->getStyle("I{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("I{$i}","5");

            $sheet->mergeCells("J{$i}:K{$i}");
            $sheet->getStyle("J{$i}:K{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("J{$i}","6");

            $sheet->getStyle("L{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("L{$i}","7");

            $sheet->getStyle("M{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("M{$i}","8");

            $sheet->getStyle("N{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("N{$i}","9");

            $sheet->getStyle("O{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("O{$i}","10");

            $sheet->getStyle("P{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("P{$i}","10");

            $sheet->getStyle("Q{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("Q{$i}","12");

            $sheet->getStyle("T{$i}")->applyFromArray($styleBorder);
            $sheet->setCellValue("T{$i}",$last[$i]);
        }

        $sheet->mergeCells("A25:J25");
        $sheet->getStyle("A25:J25")->applyFromArray($styleBorder);
        $sheet->mergeCells("K25:T25");
        $sheet->getStyle("K25:T25")->applyFromArray($styleBorder);

        $sheet->setCellValue("A25", $this->header_1);
        $sheet->setCellValue("K25", $this->header_2);

        $sheet->mergeCells("A26:J26");
        $sheet->mergeCells("K26:T26");
        $sheet->setCellValue("A26", $this->header_sub_1);
        $sheet->getStyle("A26", $styleCenterH);
        $sheet->setCellValue("K26", $this->header_sub_2);
        $sheet->getStyle("K26", $styleCenterH);
    }




}

class Tab85k extends F2NK {
    private $connection;
    private $params;

    public function __construct($connection, $params)
    {

        $this->connection   = $connection;
        $this->params       = $params;
    }

    protected function createExcelPart($sheet) {
//        add header title
        $this->positionX = 0;
        foreach ($this->rowHeaderTitles as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            ++$this->positionY;
        }
        $this->positionY++;

//        add header table
        $this->positionX = 1;
        foreach ($this->rowHeaderTable as $value) {

            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add sub header table
        $this->positionX = 1;
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;


//        add count all value
        $this->positionX = 0;
        foreach ($this->leftRow as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;

//        add code all value
        $this->positionX = 1;
        foreach ($this->leftRowCode as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;



        $counts = $this->connection->getCountStudentByGender($this->params);

        $this->positionX = 2;
        $total = 0;

        foreach ($this->rowData as $item) {
            if(isset($counts['total'][$item])){
                $value = $counts['total'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
        $this->positionY++;

//        add count by female
        $this->positionX = 2;
        $total = 0;
        foreach ($this->rowData as $item) {
            if(isset($counts['female'][$item])){
                $value = $counts['female'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
    }
}


class TabL extends F2NK {
    private $connection;
    private $params;

    public function __construct($connection, $params)
    {

        $this->connection   = $connection;
        $this->params       = $params;
    }

    protected function createExcelPart($sheet) {
//        add header title
        $this->positionX = 0;
        foreach ($this->rowHeaderTitles as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            ++$this->positionY;
        }
        $this->positionY++;

//        add header table
        $this->positionX = 1;
        foreach ($this->rowHeaderTable as $value) {

            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;

//        add sub header table
        $this->positionX = 1;
        foreach ($this->rowSubHeaderTable as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionX++;
        }
        $this->positionY++;


//        add count all value
        $this->positionX = 0;
        foreach ($this->leftRow as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;

//        add code all value
        $this->positionX = 1;
        foreach ($this->leftRowCode as $value) {
            $sheet->setCellValueByColumnAndRow($this->positionX,$this->positionY,$value);
            $this->positionY++;
        }
        $this->positionY = $this->positionY-2;



        $counts = $this->connection->getCountStudentByGender($this->params);

        $this->positionX = 2;
        $total = 0;

        foreach ($this->rowData as $item) {
            if(isset($counts['total'][$item])){
                $value = $counts['total'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
        $this->positionY++;

//        add count by female
        $this->positionX = 2;
        $total = 0;
        foreach ($this->rowData as $item) {
            if(isset($counts['female'][$item])){
                $value = $counts['female'][$item];
            } else {
                $value = 0;
            }
            $total += $value;
            $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $value);
            $this->positionX++;
        }

        $sheet->setCellValueByColumnAndRow($this->positionX, $this->positionY, $total);
        $this->positionX++;
    }
}
