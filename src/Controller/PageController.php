<?php
namespace App\Controller;

use Cake\Event\Event;
use ControllerAction\View\Helper\ControllerActionHelper;
use Page\Controller\PageController as BaseController;
use Cake\ORM\Entity;
use Page\Model\Entity\PageElement;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use DateTime;

class PageController extends BaseController
{
    public $helpers = ['Page.Page'];

    public function initialize()
    {
        parent::initialize();

        $labels = [
            'openemis_no' => 'OpenEMIS ID',
            'modified' => 'Modified On',
            'modified_user_id' => 'Modified By',
            'created' => 'Created On',
            'created_user_id' => 'Created By'
        ];

        $this->Page->config('sequence', 'order');
        $this->Page->config('is_visible', 'visible');
        $this->Page->config('labels', $labels);

        $this->loadComponent('Page.RenderLink');
        $this->loadComponent('RenderDate');
        $this->loadComponent('RenderTime');
        $this->loadComponent('RenderDatetime');
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.beforeRender'] = ['callable' => 'beforeRender', 'priority' => 5];
        $events['Controller.Page.onRenderBinary'] = 'onRenderBinary';
        $events['Controller.shutdown'] = 'shutdown';
        return $events;
    }

    // MonPib
    public function shutdown(Event $event)
    {
        $referer = Router::parse($this->request->referer('/', true));
        $refererAction = isset($referer['action']) && $referer['action'] === 'dashboard' ? true : false;
        if ($refererAction && !$this->request->query('base64') && $this->request->is('ajax')) {
            $result = $this->getAjaxResponse($event->subject());
            $this->response->body(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $this->response;
        }
    }

    public function getAjaxResponse($controller)
    {
        $action = $controller->request->action;
        $model = TableRegistry::get($controller->modelClass);
        $view = $controller->View;

        $navigations = [];
        if (isset($controller->viewVars['_navigations'])) {
            foreach ($controller->viewVars['_navigations'] as $key => $navigation) {
                if (isset($navigation['parent']) && $navigation['parent'] == 'Institutions.Students.index') {
                    $navigations[$key] = $navigation;
                }
            }
        }

        $result = [
            'data_response' => isset($controller->viewVars['data']) ? $controller->viewVars['data'] : [],
            'data_response_header' => $view && $view->Page ? $view->Page->getTableHeaders() : [],
            'toolbarButtons' => isset($controller->viewVars['toolbars']) ? $controller->viewVars['toolbars'] : [],
            'tabElements' => isset($controller->viewVars['tabs']) ? $controller->viewVars['tabs'] : [],
            'tabSelected' => isset($controller->viewVars['menuItemSelected']) && count($controller->viewVars['menuItemSelected']) > 0 ? $controller->viewVars['menuItemSelected'][0] : [],
            'navigations' => $navigations,
            'options' => ['limit' => 10],
            'byPageController' => true,
            'elements' => $controller->viewVars['elements'],
            'ajax' => 2
        ];

        if ($action === 'index') {
            $data = $view->Page->getTableData();
            $dataResult = [];
            foreach($data as $key => $datum) {
                $dataItem = array_pop($datum);
                if($result['data_response']) {
                    foreach($result['data_response'] as $ikey => $item) {
                        if($key === $ikey && isset($item['rowActions'])) {
                            array_push($datum, $item['rowActions']);
                        }
                    }
                }
                $dataResult[] = $datum;
            }
            $result['data_response'] = $dataResult;
        }

        $paramsPass = $controller->request->params['pass'];
        $action = 'index';

        if (in_array($controller->request->params['action'], ['add', 'remove', 'delete', 'edit', 'view', 'index'])) {
            $action = $controller->request->params['action'];
        }
        elseif (count($paramsPass) > 0 && !is_numeric($paramsPass[0])) {
            $action = array_shift($paramsPass);
        }

        $result['action'] = $action;

        if ($action == 'edit') {
            /*$controllerActionHelper = new ControllerActionHelper(new \Cake\View\View());
            $result['data_response_attr'] = $controllerActionHelper->getEditElementsAjax($controller->viewVars['data'], $model);*/
            $result['data_response_attr'] = [];
            foreach($result['elements'] as $name => $element) {
                $visible = ['view' => false, 'edit' => false, 'add' => false];
                if (is_array($element['visible'])) {
                    $visible = $element['visible'];
                } else if ($element['visible']) {
                    $visible = ['view' => true, 'edit' => true, 'add' => true];
                }

                $result['data_response_attr'][$name] = [
                    'attr' => [
                        'className' => '',
                        'null' => $element['attributes']['required'],
                        'field' => $element['key'],
                        'type' => $element['controlType'],
                        'label' => $element['label'],
                        'value' => $element['attributes']['value'],
                        'visible' => $visible
                    ],
                    'option' => [
                        'label' => $element['label']
                    ],
                    'type' => $element['controlType']
                ];
            }
            $result['data_form_action'] = Router::fullBaseUrl() . $this->request->here(true);

            $controllerActionHelper = new ControllerActionHelper(new \Cake\View\View());
            $formOptions = $controllerActionHelper->getFormOptions();
            $form = $controllerActionHelper->Form->create($model, $formOptions);
            $form.= $controllerActionHelper->Form->end();
            $result['data_form'] = $form;

            $doc = new \DOMDocument();
            $doc->loadHTML($result['data_form']);
            $xpath = new \DOMXPath($doc);
            $inputs = $xpath->query("//input[@type='hidden']");
            $params = [];
            foreach ($inputs as $input) {
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                $value = $name == '_Token[unlocked]' && empty($value) ? 'submit' : urlencode($value);
                $params[]= $name.'='.$value;
            }
            parse_str(implode('&', $params), $result['data_form_hidden']);

        }
        if ($action == 'index') {
            foreach ($result['data_response'] as &$row) {
                foreach ($row as &$field) {
                    if (is_array($field)) {
                        foreach ($field as &$button) {
                            if (isset($button['url'])) {
                                $button['urlBuild'] = Router::url($button['url'], true);
                            }
                        }
                    }
                }
            }
            $result['paging'] = null;
            if (!empty($controller->request->params['paging']) && count($controller->request->params['paging']))
                $result['paging'] = array_values($controller->request->params['paging'])[0];
        }

        foreach ($result['tabElements'] as $action => &$tabElement) {
            $tabElement['active'] = $action == $result['tabSelected'] || $tabElement['url']['controller'] == $result['tabSelected'] || count($result['tabElements']) == 1;
        }

        $_controller = $this->request->params['controller'];
        $_action = $this->request->params['action'];
        $_pass = [];
        if (!empty($this->request->pass)) {
            $_pass = $this->request->pass;
        } else {
            $_pass[0] = '';
        }
        foreach ($result['navigations'] as $key => &$value) {
            $value['active'] = false;
            $linkName = $_controller.'.'.$_action;
            $controllerActionLink = $linkName;
            if (!empty($_pass[0])) {
                $linkName .= '.'.$_pass[0];
            }
            if ($linkName == $key || $controllerActionLink == $key) {
                $value['active'] = true;
            }
            elseif (isset($value['selected'])) {
                if (in_array($linkName, $value['selected']) || in_array($controllerActionLink, $value['selected'])) {
                    $value['active'] = true;
                }
            }
        }
        foreach ($result['indexButtons'] as &$button) {
            $button['urlBuild'] = Router::url($button['url'], true);
        }
        foreach ($result['toolbarButtons'] as &$button) {
            if (isset($button['url']))
                $button['urlBuild'] = Router::url($button['url'], true);
            elseif (isset($button['data']['url'])) {
                $button['url'] = array_merge(Router::parse($this->request->here), $this->request->query, $button['data']['url']);
                unset($button['url']['_matchedRoute']);
                $button['urlBuild'] = Router::url($button['url'], true);
            }
        }
        foreach ($result['navigations'] as $key => &$button) {
            $button['title'] = __($button['title']);
            $button['urlBuild'] = Router::url($this->getLink($key, $button['params']), true);
        }
        foreach ($result['tabElements'] as &$button) {
            $button['text'] = isset($button['title']) ? __($button['title']) : __($button['text']);
            $button['urlBuild'] = Router::url($button['url'], true);
        }

        return $result;
    }

    function getLink($controllerActionModelLink, $params = [])
    {
        $url = ['plugin' => null, 'controller' => null, 'action' => null];
        if (isset($params['plugin'])) {
            $url['plugin'] = $params['plugin'];
            unset($params['plugin']);
        }

        $link = explode('.', $controllerActionModelLink);

        if (isset($link[0])) {
            $url['controller'] = $link[0];
        }
        if (isset($link[1])) {
            $url['action'] = $link[1];
        }
        if (isset($link[2])) {
            $url['0'] = $link[2];
        }
        if (!empty($params)) {
            $url = array_merge($url, $params);
        }
        return $url;
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

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

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->initializeToolbars();
    }

    function getWithDrawnData(){
        $Users = TableRegistry::get('User.Users');
        $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
        $statuses = $StudentStatuses->findCodeList();
        $genderOptions = $Users->Genders->getList();
        $InstitutionStudents = TableRegistry::get('Institution.Students');
        $institutionId = 1186;
        $currentAcademPeriodId = 31;
        $data = $this->getWithDrawnDeparted($InstitutionStudents, $institutionId, $statuses, $currentAcademPeriodId, $genderOptions);
        print_r($data);die;

    }

    function getWithDrawnDeparted($InstitutionStudents, $institutionId, $statuses, $currentAcademPeriodId, $genderOptions){
        $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $statuses['WITHDRAWN'], $institutionId, $currentAcademPeriodId);
        $studentsByGradeConditions['StudentWithdraw.institution_id'] = $institutionId;
        $studentsByGradeConditions['StudentWithdraw.academic_period_id'] = $currentAcademPeriodId;

        $query = $InstitutionStudents->find();
        $studentRecords = $query
            ->select([
                'StudentWithdrawReasons.international_code',
                'gender_name' => 'Genders.name'
            ])
            ->contain([
                'Users.Genders'
            ])
            ->join(['table'=> 'institution_student_withdraw', 'alias' => 'StudentWithdraw', 'type' => 'INNER', 'conditions' => 'StudentWithdraw.student_id = ' . $InstitutionStudents->aliasField('student_id')])
            ->join(['table'=> 'student_withdraw_reasons', 'alias' => 'StudentWithdrawReasons', 'type' => 'INNER', 'conditions' => 'StudentWithdrawReasons.id = StudentWithdraw.student_withdraw_reason_id'])
            ->where($studentsByGradeConditions)
            ->group([
                $InstitutionStudents->aliasField('student_id'),
                'Genders.name',
            ])
            ->toArray()
        ;
        $dataSet = [];
        foreach ($genderOptions as $key => $value){
            $dataSet['Total_WithDrawn'][$value] = 0;
            $dataSet['Total_Departed'][$value] = 0;
        }

        foreach ($studentRecords as $studentRecord){
            $genderName = $studentRecord->gender_name;
            $international_code = $studentRecord->StudentWithdrawReasons['international_code'];
            if($international_code == 'EXPELLED' || $international_code == 'ACADEMIC LEAVE')
                $dataSet['Total_WithDrawn'][$genderName] += 1;
            if($international_code == 'DISMISSAL DUE TTTOTD' || $international_code == 'EXPULSION DUE TO TTAFOS' || $international_code == 'TRANSFER TO ANOTHER ORGANIZATION')
                $dataSet['Total_Departed'][$genderName] += 1;
        }
        return $dataSet;
    }


    function getStaffByInstitution(){
        $institutionId = 58;
        $InstitutionStaff = TableRegistry::get('Institution.Staff');
        $AcademicPeriod = TableRegistry::get('AcademicPeriod.AcademicPeriods');
        $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
        $Users = TableRegistry::get('User.Users');
        $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
        $params = [
            'conditions' => ['institution_id' => $institutionId, 'staff_status_id' => $assignedStatus]
        ];
        $_conditions = [];
        $conditions = isset($params['conditions']) ? $params['conditions'] : [];
        foreach ($conditions as $key => $value) {
            $_conditions[$InstitutionStaff->alias().'.'.$key] = $value;
        }

        $staffsByPositionConditions = ['Genders.name IS NOT NULL'];
        $staffsByPositionConditions = array_merge($staffsByPositionConditions, $_conditions);

        $genderOptions = $Users->Genders->getList();
        $dataSet = array();
        $educationArray = ['higher_education' => 0, 'incomplete_higher_education'=> 0, 'spo_higher_education'=> 0, 'other'=>0];
        foreach ($genderOptions as $key => $value) {
            $dataSet[$value] = ['name' => $value, 'data' => $educationArray];
        }
        $dataSet['Total'] = ['name' => 'Total'];
        foreach ($genderOptions as $key => $value){
            $dataSet['Total'][$value] = 0;
        }

        $query = $InstitutionStaff->find('all');
        $staffArray = $query
            ->select([
                $InstitutionStaff->aliasField('staff_id'),
                $InstitutionStaff->aliasField('institution_id'),
                $InstitutionStaff->aliasField('institution_position_id'),
                'QualificationLevels.international_code',
                'Users.id',
                'Genders.name',
                'StaffQualification.qualification_title_id',
                'pos_type'=> $query->func()->max("StaffPositionTitles.type = 1"),
                'MASTER'=> $query->func()->max("QualificationLevels.international_code IN ('MASTER')"),
                'SPECIALIST'=> $query->func()->max("QualificationLevels.international_code IN ('SPECIALIST')"),
                'BACHELOR'=> $query->func()->max("QualificationLevels.international_code IN ('BACHELOR')"),
                'SPO'=> $query->func()->max("QualificationLevels.international_code IN ('SVE')"),
            ])
            ->contain([
                'Users.Genders',
                'Positions'=>['StaffPositionTitles'],
            ])
            ->join(['table'=> 'staff_qualifications', 'alias' => 'StaffQualification', 'type' => 'LEFT', 'conditions' => 'StaffQualification.staff_id = Staff.staff_id'])
            ->join(['table'=> 'qualification_titles', 'alias' => 'QualificationTitles', 'type' => 'LEFT', 'conditions' => 'StaffQualification.qualification_title_id = QualificationTitles.id'])
            ->join(['table'=> 'qualification_levels', 'alias' => 'QualificationLevels', 'type' => 'LEFT',
                'conditions' => 'QualificationTitles.qualification_level_id = QualificationLevels.id'])
            ->where($staffsByPositionConditions)
            ->group([
                $InstitutionStaff->aliasField('staff_id'),
                'Genders.name'
            ])
            ->toArray()
        ;

        foreach ($staffArray as $keyStaff => $staffItem){
            $pos_type = $staffItem->pos_type;
            $gender = $staffItem->user->gender->name;

            $MASTER = $staffItem->MASTER;
            $SPECIALIST = $staffItem->SPECIALIST;
            $BACHELOR = $staffItem->BACHELOR;
            $SPO = $staffItem->SPO;

            $dataSet['Total'][$gender] += 1;
            if($pos_type){
                if($MASTER || $SPECIALIST){
                    $dataSet[$gender]['data']['higher_education'] += 1;
                }elseif($BACHELOR){
                    $dataSet[$gender]['data']['incomplete_higher_education'] += 1;
                }elseif($SPO){
                    $dataSet[$gender]['data']['spo_higher_education'] += 1;
                }else{
                    $dataSet[$gender]['data']['other'] += 1;
                }
            }
        }
        print_r($dataSet);die;
    }





    function getDataStudentEnrolledAza(){
        $Users = TableRegistry::get('User.Users');
        $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
        $statuses = $StudentStatuses->findCodeList();
        $InstitutionClassStudents = TableRegistry::get('Institution.InstitutionClassStudents');
        $genderOptions = $Users->Genders->getList();
        $InstitutionStudents = TableRegistry::get('Institution.Students');
        $institutionId = 2402;
        $currentAcademPeriodId = 31;

        $dataSet = [];
        $programmes = [];
        foreach ($genderOptions as $key => $value){
            $dataSet[$value] = array('name' => $value, 'data_disablity' => array(), 'data' => array());
        }
        //if($is_check_graduated_enrolled_student){

        $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $InstitutionClassStudents, $statuses, $institutionId, $currentAcademPeriodId);
        $studentsByGradeConditions[] = "(EducationStages.code = '1 college' OR EducationStages.code = '1 lyceum' OR EducationStages.code = '1 course' OR EducationStages.code = '1 class' OR EducationStages.code = 'Nursery')";
        $query = $InstitutionStudents->find();
        $start_date = date('Y-m-d');
        $disabilityCase = $query->newExpr()
            ->addCase(
                $query->newExpr()->add(["'" . $start_date . "' BETWEEN UserPeopleDisabilities.from_disability AND UserPeopleDisabilities.to_disability"]),
                1,
                'integer'
            );

        $studentByProgramme = $query
            ->select([
                'transfer_international_code' => 'InstitutionReasonForTransfer.international_code',
                'EducationGrades.education_stage_id',
                'programme_id' => 'EducationProgrammes.id',
                'programme_name' => 'EducationProgrammes.name',
                'gender_name' => 'Genders.name',
                'disablity_count' => $query->func()->count($disabilityCase),
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'EducationGrades.EducationStages',
                'EducationGrades.EducationProgrammes',
                'Users.Genders'
            ])
            ->join(['table'=> 'institution_reason_for_transfer', 'alias' => 'InstitutionReasonForTransfer', 'type' => 'LEFT', 'conditions' => 'InstitutionReasonForTransfer.id = ' . $InstitutionStudents->aliasField('institution_reason_for_transfer_id')])
            ->join(['table' => 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
            ->where($studentsByGradeConditions)
            ->group([
                'EducationProgrammes.id',
                'Genders.name'
            ])
            ->toArray();
        //print_r($studentByProgramme);die;
        $sss = 0;
        if (count($studentByProgramme) > 0) {
            foreach ($studentByProgramme as $key => $programmeItem) {
                $transfer_international_code = $programmeItem->transfer_international_code;
                $programme_id = $programmeItem->programme_id;
                $programme_name = $programmeItem->programme_name;
                $disablity_count = $programmeItem->disablity_count;
                $gender_name = $programmeItem->gender_name;
                $total = $programmeItem->total;

                if($transfer_international_code == 'PRIMAL' || $transfer_international_code == 'TRANSFER FROM ANOTHER FORM OF STUDY' || $transfer_international_code == 'TRANSFER FROM ANOTHER SPECIALTY'
                    || $transfer_international_code == 'TRANSFER FROM ANOTHER PAYMENT FORM' || $transfer_international_code == 'TRANSFER FROM ANOTHER ORGANIZATION'){


                    $programmes[$programme_id] = $programme_name;
                    foreach ($dataSet as $dkey => $dvalue) {
                        if (!array_key_exists($programme_id, $dataSet[$dkey]['data'])) {
                            if (!array_key_exists($programme_id, $dataSet[$dkey]['data'])) {
                                $dataSet[$dkey]['data_disablity'][$programme_id] = 0;
                                $dataSet[$dkey]['data'][$programme_id] = 0;
                            }
                        }
                    }
                    $dataSet[$gender_name]['data'][$programme_id] += $total;
                    $sss += $total;
                    $dataSet[$gender_name]['data_disablity'][$programme_id] += $disablity_count;
                }
            }
        }
        //}

        $dataSet['Programmes'] = $programmes;
        print_r($dataSet);die;
    }




    function calculateStaff(){
        $InstitutionStaff = TableRegistry::get('Institution.Staff');
        //$StaffAppraisals = TableRegistry::get('Institution.StaffAppraisals');
        $InstitutionPositions = TableRegistry::get('Institution.InstitutionPositions');
        $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
        $Users = TableRegistry::get('User.Users');
        $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
        $params = [
            'conditions' => ['institution_id' => 78, 'staff_status_id' => $assignedStatus]
        ];
        $_conditions = [];
        $conditions = isset($params['conditions']) ? $params['conditions'] : [];
        foreach ($conditions as $key => $value) {
            $_conditions[$InstitutionStaff->alias().'.'.$key] = $value;
        }
        $_conditions['StaffPositionTitles.type'] = 1;
        //$_conditions['UserEmployments.employment_spec'] = 0;
        //$staffsByPositionConditions = ['Genders.name IS NOT NULL'];




        $genderOptions = $Users->Genders->getList();
        $dataSet = array();
        $educationArray = ['higher_education' => 0, 'incomplete_higher_education'=> 0, 'spo_higher_education'=> 0, 'other'=>0];
        foreach ($genderOptions as $key => $value) {
            $dataSet[$value] = ['name' => $value, 'data' => $educationArray];
        }
        $dataSet['Total'] = ['name' => 'Total'];
        foreach ($genderOptions as $key => $value){
            $dataSet['Total'][$value] = 0;
        }

        $curDate = date('Y-m-d');
        $begin_date = date('Y-m-d', strtotime("-5 year", strtotime($curDate)));
        $end_date = date('Y-m-d', strtotime("-1 day", strtotime($curDate)));

        $staffsByPositionConditions = ["InstitutionStaffAppraisals.appraisal_period_to BETWEEN '$begin_date' - INTERVAL 5 YEAR AND '$end_date'"];
        $staffsByPositionConditions = array_merge($staffsByPositionConditions, $_conditions);
        $query = $InstitutionStaff->find();
        $staffArray = $query
            ->select([
                'total' => $query->func()->count("DISTINCT " . $InstitutionStaff->aliasField('staff_id'))
            ])
            ->contain([
                'Positions'=>['StaffPositionTitles']
            ])
            ->join(['table'=> 'institution_staff_appraisals', 'alias' => 'InstitutionStaffAppraisals', 'type' => 'LEFT', 'conditions' => 'InstitutionStaffAppraisals.staff_id = ' . $InstitutionStaff->aliasField('staff_id')])
            ->where($staffsByPositionConditions)
            ->toArray()
        ;

        print_r($staffArray);die;


        $query = $InstitutionStaff->find();
        $staffArray = $query
            ->select([
                $InstitutionStaff->aliasField('institution_id'),
                $InstitutionStaff->aliasField('institution_position_id'),
                'QualificationLevels.international_code',
                'Users.id',
                'Genders.name',
                'StaffQualification.qualification_title_id',
                'pos_type' => 'StaffPositionTitles.type',
                'total' => $query->func()->count("DISTINCT " . $InstitutionStaff->aliasField('staff_id'))
            ])
            ->contain([
                'Users.Genders',
                'Positions'=>['StaffPositionTitles']
            ])
            ->join(['table'=> 'staff_qualifications', 'alias' => 'StaffQualification', 'type' => 'LEFT', 'conditions' => 'StaffQualification.staff_id = ' . $InstitutionStaff->aliasField('staff_id')])
            ->join(['table'=> 'qualification_titles', 'alias' => 'QualificationTitles', 'type' => 'LEFT', 'conditions' => 'StaffQualification.qualification_title_id = QualificationTitles.id'])
            ->join(['table'=> 'qualification_levels', 'alias' => 'QualificationLevels', 'type' => 'LEFT', 'conditions' => 'QualificationTitles.qualification_level_id = QualificationLevels.id'])
            ->where($staffsByPositionConditions)
            ->group([
                'QualificationLevels.international_code',
                'Genders.name','StaffPositionTitles.type'
            ])
            ->toArray()
        ;
        foreach ($staffArray as $keyStaff => $staffItem){
            $total = $staffItem->total;
            $pos_type = $staffItem->pos_type;
            $gender = $staffItem->user->gender->name;

            $international_code_level = $staffItem->QualificationLevels['international_code'];
            if($pos_type) {
                if ($international_code_level == 'SPO') {
                    $dataSet[$gender]['data']['spo_higher_education'] += $total;
                } elseif ($international_code_level == 'SPECIALIST' || $international_code_level == 'MASTER' || $international_code_level == 'BACHELOR') {
                    $dataSet[$gender]['data']['higher_education'] += $total;
                } else {
                    $dataSet[$gender]['data']['other'] += $total;
                }
            }
            $dataSet['Total'][$gender] += $total;
        }

        print_r($dataSet);die;
    }

    function getSubjectsByStaff(){
        $InstitutionSubjectStaff = TableRegistry::get('Institution.InstitutionSubjectStaff');
        $Users = TableRegistry::get('User.Users');
        $genderOptions = $Users->Genders->getList();
        $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
        $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
        $institutionId = 78;
        $currentAcademPeriodId = 31;
        $_conditions = [
            $InstitutionSubjectStaff->aliasField('institution_id') => $institutionId,
            'InstitutionSubjects.institution_id' => $institutionId,
            'InstitutionSubjects.academic_period_id' => $currentAcademPeriodId,
            'InstitutionStaff.staff_status_id' => $assignedStatus
        ];

        $query = $InstitutionSubjectStaff->find();
        $subjectArray = $query
            ->select([
                'Genders.name',
                'InstitutionSubjects.name',
                'EducationStages.code',
                'EducationSubjects.code'
            ])
            ->join(['table'=> 'security_users', 'alias' => 'SecurityUsers', 'type' => 'INNER', 'conditions' => 'SecurityUsers.id = ' . $InstitutionSubjectStaff->aliasField('staff_id')])
            ->join(['table'=> 'genders', 'alias' => 'Genders', 'type' => 'INNER', 'conditions' => 'Genders.id = SecurityUsers.gender_id'])
            ->join(['table'=> 'institution_staff', 'alias' => 'InstitutionStaff', 'type' => 'INNER', 'conditions' => 'InstitutionStaff.staff_id = ' . $InstitutionSubjectStaff->aliasField('staff_id')])
            ->join(['table'=> 'institution_subjects', 'alias' => 'InstitutionSubjects', 'type' => 'INNER', 'conditions' => 'InstitutionSubjects.id = ' . $InstitutionSubjectStaff->aliasField('institution_subject_id')])
            ->join(['table'=> 'education_subjects', 'alias' => 'EducationSubjects', 'type' => 'INNER', 'conditions' => 'EducationSubjects.id = InstitutionSubjects.education_subject_id'])
            ->join(['table'=> 'institution_class_subjects', 'alias' => 'InstitutionClassSubjects', 'type' => 'INNER', 'conditions' => 'InstitutionClassSubjects.institution_subject_id = InstitutionSubjects.id'])
            ->join(['table'=> 'institution_class_grades', 'alias' => 'InstitutionClassGrades', 'type' => 'INNER', 'conditions' => 'InstitutionClassGrades.institution_class_id = InstitutionClassSubjects.institution_class_id'])
            ->join(['table'=> 'education_grades', 'alias' => 'EducationGrades', 'type' => 'INNER', 'conditions' => 'EducationGrades.id = InstitutionClassGrades.education_grade_id'])
            ->join(['table'=> 'education_stages', 'alias' => 'EducationStages', 'type' => 'INNER', 'conditions' => 'EducationStages.id = EducationGrades.education_stage_id'])
            ->where($_conditions)
            ->group([
                $InstitutionSubjectStaff->aliasField('staff_id'),
                'Genders.name',
                $InstitutionSubjectStaff->aliasField('institution_subject_id')
            ])
            ->toArray()
        ;
        $dataSet = [];
        $gendersValue = [];
        foreach ($genderOptions as $key => $value){
            $gendersValue[$value] = 0;
        }
        $gendersValue['name'] = '';

        if(count($subjectArray) > 0){
            $dataSet['1_4'] = $gendersValue;
            $dataSet['1_4']['name'] = '1-4 классы';
            foreach($subjectArray as $keyS=>$itemS){
                $genderName = $itemS->Genders['name'];
                $subjectName = $itemS->InstitutionSubjects['name'];
                $subjectCode = $itemS->EducationSubjects['code'];
                $stageCode = $itemS->EducationStages['code'];
                if($stageCode == '1 class' || $stageCode == '2 class' || $stageCode == '3 class' || $stageCode == '4 class'){
                    $dataSet['1_4'][$genderName] += 1;
                }else{
                    if (!array_key_exists($subjectCode, $dataSet)){
                        $dataSet[$subjectCode] = $gendersValue;
                        $dataSet[$subjectCode]['name'] = $subjectName;
                    }
                    $dataSet[$subjectCode][$genderName] += 1;
                }
            }
        }
        print_r($dataSet);die;
    }

    function getAttendanceaza(){
        $InstitutionAttendance = TableRegistry::get('Institution.InstitutionAttendance');
        $periodId = 1;
        $institutionId = 78;
        $currentAcademPeriodId = 31;
        if($periodId) {
            $_conditions = [
                $InstitutionAttendance->aliasField('institution_id') => $institutionId,
                $InstitutionAttendance->aliasField('academic_period_id') => $currentAcademPeriodId,
                $InstitutionAttendance->aliasField('institution_period_id') => $periodId
            ];
            $query = $InstitutionAttendance->find();
            $attendanceArray = $query
                ->select([
                    $InstitutionAttendance->aliasField('attendance_percentage')
                ])
                ->contain([
                    'InstitutionPeriod'
                ])
                ->where($_conditions)
                ->first();
            if (count($attendanceArray) > 0) {
                $attendance_percentage = $attendanceArray->attendance_percentage;
                if($attendance_percentage)
                    echo $attendance_percentage;die;
            }
        }
        die;
        return 0;
    }


    function getPerfomance(){
        $curDate = date('Y-m-d');
        $InstitutionAggregatedDataVpo = TableRegistry::get('Institution.InstitutionAggregatedDataVpo');
        $InstitutionTypes = TableRegistry::get('Institution.Types');
        $InstitutionPeriod = TableRegistry::get('Institution.InstitutionPeriod');
        $allInstitutionTypes = $this->getAllInstitutionTypes($InstitutionTypes);
        $institutionTypesByPeriod = $this->getInstitutionTypesByPeriod($InstitutionPeriod, $allInstitutionTypes, $curDate);

        $institutionId = 3987;
        $currentAcademPeriodId = 31;
        $institution_type_id = 20;


        $periodId = $institutionTypesByPeriod[$institution_type_id];

        if($periodId){
            $_conditions = [
                $InstitutionAggregatedDataVpo->aliasField('institution_id') => $institutionId,
                $InstitutionAggregatedDataVpo->aliasField('academic_period_id') => $currentAcademPeriodId,
                $InstitutionAggregatedDataVpo->aliasField('institution_period_id') => $periodId
            ];
            $query = $InstitutionAggregatedDataVpo->find();
            $aggregatedDataVpoRecord = $query
                ->contain([
                    'InstitutionPeriod'
                ])
                ->where($_conditions)
                ->first();
            if(count($aggregatedDataVpoRecord) > 0){
                $dataSet = $this->putDataSetByPerfomance($institution_type_id, $aggregatedDataVpoRecord);
                print_r($dataSet);die;
            }
            /*if (count($attendanceArray) > 0) {
                $attendance_percentage = $attendanceArray->attendance_percentage;
                if($attendance_percentage)
                    return $attendance_percentage;
            }*/
        }
        die;
    }

    function putDataSetByPerfomance($institution_type_id, $aggregatedDataVpoRecord){
        switch ($institution_type_id){
            case 2:
                $dataSet = ['per_excellent_2_4' => $aggregatedDataVpoRecord->od_excellent, 'per_drummers_2_4' => $aggregatedDataVpoRecord->od_good_excellent, 'per_critics_2_4' => $aggregatedDataVpoRecord->od_good_excellent,
                            'per_excellent_5_9' => $aggregatedDataVpoRecord->od_excellent_5_9, 'per_drummers_5_9' => $aggregatedDataVpoRecord->od_good_excellent_5_9, 'per_critics_5_9' => $aggregatedDataVpoRecord->od_satisfactorily_5_9,
                            'per_excellent_10_11' => $aggregatedDataVpoRecord->od_excellent_10_11, 'per_drummers_10_11' => $aggregatedDataVpoRecord->od_good_excellent_10_11, 'per_critics_10_11' => $aggregatedDataVpoRecord->od_satisfactorily_10_11];
                break;
            default:
                $dataSet = ['absolute_academic_performance' => $aggregatedDataVpoRecord->absolute_academic_performance, 'quality_academic_performance' => $aggregatedDataVpoRecord->quality_academic_performance];
        }
        return $dataSet;
    }

    function getInstitutionTypesByPeriod($InstitutionPeriod, $allInstitutionTypes, $curDate){
        $typesByPeriod = [];
        foreach ($allInstitutionTypes as $keyT=>$itemT) {
            $query = $InstitutionPeriod->find();
            $_conditions = [
                'InstitutionTypePeriod.institution_types_id' => $itemT
            ];
            $periodArray = $query
                ->select([
                    $InstitutionPeriod->aliasField('id'),
                    $InstitutionPeriod->aliasField('end_date')
                ])
                ->join(['table' => 'institution_type_period', 'alias' => 'InstitutionTypePeriod', 'type' => 'INNER', 'conditions' => 'InstitutionTypePeriod.institution_period_id = ' . $InstitutionPeriod->aliasField('id')])
                ->where($_conditions)
                ->order(
                    [$InstitutionPeriod->aliasField('end_date') . ' DESC']
                )
                ->toArray();

            foreach ($periodArray as $keyP => $itemP) {
                $id = $itemP->id;
                $end_date = $itemP->end_date;
                if ($end_date) {
                    $end_date = date('Y-m-d', strtotime($end_date));
                    if ($curDate > $end_date) {
                        $typesByPeriod[$itemT] = $id;
                        break;
                    }
                }
            }
        }
        return $typesByPeriod;
    }

    function getAllInstitutionTypes($InstitutionTypes){
        $types = [];
        $query = $InstitutionTypes->find();
        $institutionTypesArray = $query
            ->select([
                $InstitutionTypes->aliasField('id'),
            ])
            ->where([$InstitutionTypes->aliasField('visible')=>1])
            ->toArray()
        ;
        foreach ($institutionTypesArray as $key=>$item){
            $id = $item->id;
            $types[] = $id;
        }
        return $types;
    }

    function getStaffDataByInstitution(){
        $InstitutionStaff = TableRegistry::get('Institution.Staff');
        $institutionId = 3987;
        $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
        $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
        $AcademicPeriod = TableRegistry::get('AcademicPeriod.AcademicPeriods');
        $currentAcademPeriodId = $AcademicPeriod->getCurrent();
        $AcademicPeriodObject = $AcademicPeriod->get($currentAcademPeriodId);
        $Users = TableRegistry::get('User.Users');
        $genderOptions = $Users->Genders->getList();
        $staffData = $this->getDataStaff($InstitutionStaff, $institutionId, $assignedStatus, $AcademicPeriodObject, $genderOptions);
        print_r($staffData);die;
    }

    function getDataStaff($InstitutionStaff, $institutionId, $assignedStatus, $AcademicPeriodObject, $genderOptions){
        $params = [
            'conditions' => ['institution_id' => $institutionId, 'staff_status_id' => $assignedStatus]
        ];
        $_conditions = [];
        $conditions = isset($params['conditions']) ? $params['conditions'] : [];
        foreach ($conditions as $key => $value) {
            $_conditions[$InstitutionStaff->alias().'.'.$key] = $value;
        }

        $staffsByPositionConditions = ['Genders.name IS NOT NULL'];
        $staffsByPositionConditions = array_merge($staffsByPositionConditions, $_conditions);

        $dataSet = array();
        $allPositions = [];
        $educationArray = ['higher_education' => 0, 'incomplete_higher_education'=> 0, 'spo_higher_education'=> 0, 'other'=>0];
        foreach ($genderOptions as $key => $value) {
            $dataSet[$value] = ['name' => $value, 'data' => $educationArray];
        }
        $dataSet['Total'] = ['name' => 'Total'];
        foreach ($genderOptions as $key => $value){
            $dataSet['Total'][$value] = 0;
        }

        $query = $InstitutionStaff->find('all');
        $staffArray = $query
            ->select([
                $InstitutionStaff->aliasField('staff_id'),
                $InstitutionStaff->aliasField('institution_id'),
                $InstitutionStaff->aliasField('institution_position_id'),
                'staff_position_title_ids' => 'group_concat(StaffPositionTitles.id)',
                'position_names' => 'group_concat(StaffPositionTitles.name)',
                'position_types' => 'group_concat(StaffPositionTitles.type)',
                'QualificationLevels.international_code',
                'Users.id',
                'Genders.name',
                'StaffQualification.qualification_title_id',
                'pos_type'=> $query->func()->max("StaffPositionTitles.type = 1"),
                'MASTER'=> $query->func()->max("QualificationLevels.international_code IN ('MASTER')"),
                'SPECIALIST'=> $query->func()->max("QualificationLevels.international_code IN ('SPECIALIST')"),
                'BACHELOR'=> $query->func()->max("QualificationLevels.international_code IN ('BACHELOR')"),
                'SPO'=> $query->func()->max("QualificationLevels.international_code IN ('SVE')"),
            ])
            ->contain([
                'Users.Genders',
                'Positions'=>['StaffPositionTitles'],
            ])
            ->join(['table'=> 'staff_qualifications', 'alias' => 'StaffQualification', 'type' => 'LEFT', 'conditions' => 'StaffQualification.staff_id = Staff.staff_id'])
            ->join(['table'=> 'qualification_titles', 'alias' => 'QualificationTitles', 'type' => 'LEFT', 'conditions' => 'StaffQualification.qualification_title_id = QualificationTitles.id'])
            ->join(['table'=> 'qualification_levels', 'alias' => 'QualificationLevels', 'type' => 'LEFT',
                'conditions' => 'QualificationTitles.qualification_level_id = QualificationLevels.id'])
            ->where($staffsByPositionConditions)
            ->group([
                $InstitutionStaff->aliasField('staff_id'),
                'Genders.name'
            ])
            ->toArray()
        ;
        //print_r($staffArray);die;
        foreach ($staffArray as $keyStaff => $staffItem){
            $pos_type = $staffItem->pos_type;
            $position_names = explode(',', $staffItem->position_names);
            $position_types = explode(',', $staffItem->position_types);
            $staff_position_title_ids = explode(',', $staffItem->staff_position_title_ids);

            $gender = $staffItem->user->gender->name;
            $positionsArray = $this->getUniquePosition($position_names, $position_types, $staff_position_title_ids);
            //print_r($positionsArray);die;
            foreach ($positionsArray as $keyP => $itemP){
                $positionName = $itemP['name'];
                $positionType = $itemP['position_type'];
                if($positionType){
                    if (!array_key_exists($keyP, $allPositions)){
                        $allPositions[$keyP]['name'] = '';
                        foreach ($genderOptions as $keyG => $valueG){
                            $allPositions[$keyP][$valueG] = 0;
                        }
                    }
                    $allPositions[$keyP]['name'] = $positionName;
                    $allPositions[$keyP][$gender] += 1;
                }
            }

            $MASTER = $staffItem->MASTER;
            $SPECIALIST = $staffItem->SPECIALIST;
            $BACHELOR = $staffItem->BACHELOR;
            $SPO = $staffItem->SPO;

            $dataSet['Total'][$gender] += 1;
            if($pos_type){
                if($MASTER || $SPECIALIST){
                    $dataSet[$gender]['data']['higher_education'] += 1;
                }elseif($BACHELOR){
                    $dataSet[$gender]['data']['incomplete_higher_education'] += 1;
                }elseif($SPO){
                    $dataSet[$gender]['data']['spo_higher_education'] += 1;
                }else{
                    $dataSet[$gender]['data']['other'] += 1;
                }
            }
        }
        print_r($allPositions);die;
        return $dataSet;
    }

    function getUniquePosition($position_names, $position_types, $staff_position_title_ids){
        $tempArray = [];
        foreach($staff_position_title_ids as $key=>$item){
            $tempArray[$item]['name'] = $position_names[$key];
            $tempArray[$item]['position_type'] = $position_types[$key];
        }
        return $tempArray;
    }

    function getStudentDataByInstitution(){
        $InstitutionStudents = TableRegistry::get('Institution.Students');
        $InstitutionClassStudents = TableRegistry::get('Institution.InstitutionClassStudents');
        $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
        $statuses = $StudentStatuses->findCodeList();
        $EducationFormOfTraining = TableRegistry::get('Education.EducationFormOfTraining');
        $formOfTrainingOptions = $EducationFormOfTraining->getListWithCode();
        $Users = TableRegistry::get('User.Users');
        $genderOptions = $Users->Genders->getList();

        $currentAcademPeriodId = 31;
        $institutionId = 1129;
        $international_code_type = 'PRIMARY SECONDARY';

        $data = $this->getDataStudent($InstitutionStudents, $InstitutionClassStudents, $statuses, $formOfTrainingOptions, $institutionId,
            $currentAcademPeriodId, $international_code_type, $genderOptions);

        print_r($data);die;
    }

    function getDataStudent($InstitutionStudents, $InstitutionClassStudents, $statuses, $formOfTrainingOptions, $institutionId, $currentAcademPeriodId,
                            $international_code_type, $genderOptions){


        $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $statuses['CURRENT'], $institutionId, $currentAcademPeriodId);

        $dataSet = array();
        $arrived = [];

        $dataSet['Foreigner_Total'] = ['Male' => 0, 'Female' => 0];
        $dataSet['Total'] = ['name' => 'Total', 'Male' => 0, 'Female' => 0];

        $this->getTotalStudentData($InstitutionStudents, $studentsByGradeConditions, $dataSet);

        $studentsByGradeConditions[$InstitutionClassStudents->aliasField('student_status_id')] = $statuses['CURRENT'];
        $studentsByGradeConditions[$InstitutionClassStudents->aliasField('academic_period_id')] = $currentAcademPeriodId;
        $studentsByGradeConditions[$InstitutionClassStudents->aliasField('institution_id')] = $institutionId;
        $studentsByGradeConditions[] = $InstitutionStudents->aliasField('education_grade_id').' IS NOT NULL';


        if($international_code_type == 'PRESCHOOL EDUCATIONAL ORGANIZATION' || $international_code_type == 'CHILDREN EDUCATIONAL CENTERS'){
            $gradeArray = ['0_1'=> 0, '1_2'=> 0, '2_3'=> 0, '3_4'=> 0, '4_5'=> 0, '5_6'=> 0, '6_7'=> 0, 'other' => 0];
            foreach ($genderOptions as $key => $value) {
                $arrived[$value] = 0;
                foreach ($formOfTrainingOptions as $keyF => $valueF){
                    $dataSet[$value][$valueF] = array('name' => $value, 'data_disablity' => $gradeArray, 'data' => $gradeArray);
                }
            }
            $this->getDataStudentsDooByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataSet);
        }else{

            foreach ($genderOptions as $key => $value) {
                $arrived[$value] = 0;
                foreach ($formOfTrainingOptions as $keyF => $valueF){
                    $dataSet[$value][$valueF] = array('name' => $value, 'data_disablity' => array(), 'data' => array());
                }
            }
            $this->getDataStudentsOtherByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataSet);
        }
        return $dataSet;
    }

    function getDataStudentsOtherByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, &$dataSet){
        $query = $InstitutionStudents->find();
        $start_date = date('Y-m-d');
        $disabilityCase = $query->newExpr()
            ->addCase(
                $query->newExpr()->add(["'" . $start_date . "' BETWEEN UserPeopleDisabilities.from_disability AND UserPeopleDisabilities.to_disability"]),
                1,
                'integer'
            );
        $allowanceCase = $query->newExpr()
            ->addCase(
                $query->newExpr()->add(["'" . $start_date . "' BETWEEN SpecialNeedsUserAllowance.start_date_allowance AND SpecialNeedsUserAllowance.end_date_allowance"]),
                1,
                'integer'
            );
        $studentByGrades = $query
            ->select([
                $InstitutionStudents->aliasField('institution_id'),
                $InstitutionStudents->aliasField('education_grade_id'),
                'transfer_international_code' => 'InstitutionReasonForTransfer.international_code',
                'form_of_payment_international_code' => 'FormOfPayment.international_code',
                'ShiftOptions.name',
                'EducationGrades.name',
                'EducationGrades.education_stage_id',
                'EducationGrades.admission_age',
                'EducationStages.code',
                'EducationStages.order',
                'EducationCycles.id',
                'Users.id',
                'InstitutionClassStudents.institution_class_id',
                'InstitutionClasses.name',
                'InstitutionClasses.capacity',
                'InstitutionClasses.language_id',
                'Languages.international_code',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id',
                'EducationFormOfTraining.code',
                'disablity_count' => $query->func()->count($disabilityCase),
                'allowance_count' => $query->func()->count($allowanceCase),
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'EducationGrades.EducationStages',
                'EducationGrades.EducationProgrammes.EducationCycles.EducationLevels',
                'Users.Genders',
                'Users.FormOfPayment',
                'EducationGrades.EducationProgrammes.EducationFormOfTraining'
            ])
            ->join(['table'=> 'institution_reason_for_transfer', 'alias' => 'InstitutionReasonForTransfer', 'type' => 'LEFT', 'conditions' => 'InstitutionReasonForTransfer.id = ' . $InstitutionStudents->aliasField('institution_reason_for_transfer_id')])
            ->join(['table'=> 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
            ->join(['table'=> 'institution_class_students', 'alias' => 'InstitutionClassStudents', 'type' => 'LEFT', 'conditions' => 'InstitutionClassStudents.student_id = Students.student_id'])
            ->join(['table'=> 'institution_classes', 'alias' => 'InstitutionClasses', 'type' => 'LEFT', 'conditions' => 'InstitutionClasses.id = InstitutionClassStudents.institution_class_id'])
            ->join(['table'=> 'institution_shifts', 'alias' => 'InstitutionShifts', 'type' => 'LEFT', 'conditions' => 'InstitutionShifts.id = InstitutionClasses.institution_shift_id'])
            ->join(['table'=> 'shift_options', 'alias' => 'ShiftOptions', 'type' => 'LEFT', 'conditions' => 'ShiftOptions.id = InstitutionShifts.shift_option_id'])
            ->join(['table'=> 'languages', 'alias' => 'Languages', 'type' => 'LEFT', 'conditions' => 'Languages.id = InstitutionClasses.language_id'])
            ->join(['table'=> 'special_needs_user_allowance', 'alias' => 'SpecialNeedsUserAllowance', 'type' => 'LEFT', 'conditions' => 'SpecialNeedsUserAllowance.security_user_id = Students.student_id'])
            ->where($studentsByGradeConditions)
            ->group([
                'EducationGrades.education_stage_id',
                'InstitutionClassStudents.institution_class_id',
                'InstitutionClasses.institution_shift_id',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id',
                'Users.form_of_payment_id'
            ])
            ->order(
                ['EducationLevels.order', 'EducationCycles.order', 'EducationProgrammes.order', 'EducationStages.order']
            )
            ->toArray()
        ;


        //print_r($studentByGrades);die;
        $grades = [];
        $classes = [];
        $classesCapacity = [];
        $languages = [];
        $formOfPayments = [];
        $shifts = [];


        foreach ($studentByGrades as $key => $studentByGrade) {
            $gradeId = $studentByGrade->education_grade->education_stage_id;
            $shiftName = $studentByGrade->ShiftOptions['name'];
            $form_of_payment_international_code = $studentByGrade->form_of_payment_international_code;
            if(!$form_of_payment_international_code)
                $form_of_payment_international_code = 'other';
            $transfer_international_code = $studentByGrade->transfer_international_code;
            $gradeName = $studentByGrade->education_grade->education_stage->code;
            $gradeGender = $studentByGrade->user->gender->name;
            $gradeTotal = $studentByGrade->total;
            $disablity_count = $studentByGrade->disablity_count;

            $institutionClassesLanguage_id = $studentByGrade->InstitutionClasses['language_id'];
            $capacity = $studentByGrade->InstitutionClasses['capacity'];
            $institution_class_id = $studentByGrade->InstitutionClassStudents['institution_class_id'];
            $international_code = $studentByGrade->Languages['international_code'];

            if(empty($institutionClassesLanguage_id)){
                $institutionClassesLanguage_id = 'other';
                $international_code = 'other';
            }

            $form_of_training_name = $studentByGrade->education_grade->education_programme->education_form_of_training->code;

            $admission_age = $studentByGrade->education_grade->admission_age;
            $grades[$gradeId] = array($gradeName, $admission_age);

            $classes[$institutionClassesLanguage_id][$institution_class_id] += $gradeTotal;
            $classesCapacity[$institutionClassesLanguage_id][$institution_class_id] += $capacity;
            $languages[$institutionClassesLanguage_id] = $international_code;

            foreach ($dataSet as $fkey => $fvalue) {
                if($fkey != 'Foreigner_Total' && $fkey != 'Total') {
                    foreach ($fvalue as $dkey=>$dvalue) {
                        if (!array_key_exists($gradeId, $dataSet[$fkey][$dkey]['data'])) {
                            $dataSet[$fkey][$dkey]['data_disablity'][$gradeId] = 0;
                            $dataSet[$fkey][$dkey]['data'][$gradeId] = 0;
                        }
                    }
                }
            }
            $dataSet[$gradeGender][$form_of_training_name]['data'][$gradeId] += $gradeTotal;
            $formOfPayments[$form_of_payment_international_code] += $gradeTotal;
            $shifts[$shiftName] += $gradeTotal;
            $dataSet[$gradeGender][$form_of_training_name]['data_disablity'][$gradeId] += $disablity_count;
            if($transfer_international_code == 'PRIMAL' || $transfer_international_code == 'RECOVERY' || $transfer_international_code == 'TRANSFER FROM ANOTHER FORM OF STUDY'
                || $transfer_international_code == 'TRANSFER FROM ANOTHER SPECIALTY' || $transfer_international_code == 'TRANSFER FROM ANOTHER ORGANIZATION'
                || $transfer_international_code == 'TRANSFER FROM ANOTHER PAYMENT FORM'){
                $arrived[$gradeGender] += $gradeTotal;
            }
        }

        $dataSet['Education_Grades'] = $grades;
        $dataSet['Classes'] = $classes;
        $dataSet['Classes_Capacity'] = $classesCapacity;
        $dataSet['Languages'] = $languages;
        $dataSet['Arrived'] = $arrived;
        return $dataSet;
        //print_r($shifts);die;
    }

    function getDataStudentsDooByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, &$dataSet){
        $query = $InstitutionStudents->find();
        $start_date = date('Y-m-d');
        $disabilityCase = $query->newExpr()
            ->addCase(
                $query->newExpr()->add(["'" . $start_date . "' BETWEEN UserPeopleDisabilities.from_disability AND UserPeopleDisabilities.to_disability"]),
                1,
                'integer'
            );
        $studentByGrades = $query
            ->select([
                $InstitutionStudents->aliasField('institution_id'),
                $InstitutionStudents->aliasField('education_grade_id'),
                'EducationGrades.name',
                'age' => $query->newExpr('TIMESTAMPDIFF( YEAR, Users.date_of_birth, CURDATE( ) )'),
                'Users.id',
                'InstitutionClassStudents.institution_class_id',
                'InstitutionClasses.name',
                'InstitutionClasses.language_id',
                'InstitutionClasses.capacity',
                'Languages.international_code',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id',
                'EducationFormOfTraining.code',
                'disablity_count' => $query->func()->count($disabilityCase),
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'Users.Genders',
                'EducationGrades.EducationProgrammes.EducationFormOfTraining'
            ])
            ->join(['table'=> 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
            ->join(['table'=> 'institution_class_students', 'alias' => 'InstitutionClassStudents', 'type' => 'LEFT', 'conditions' => 'InstitutionClassStudents.student_id = Students.student_id'])
            ->join(['table'=> 'institution_classes', 'alias' => 'InstitutionClasses', 'type' => 'LEFT', 'conditions' => 'InstitutionClasses.id = InstitutionClassStudents.institution_class_id'])
            ->join(['table'=> 'languages', 'alias' => 'Languages', 'type' => 'LEFT', 'conditions' => 'Languages.id = InstitutionClasses.language_id'])
            ->where($studentsByGradeConditions)
            ->group([
                'age',
                'InstitutionClassStudents.institution_class_id',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id'
            ])
            ->toArray()
        ;

        $grades = ['0_1'=> array('0-2', 1), '1_2'=> array('0-2', 1), '2_3'=> array('2-3', 2), '3_4'=> array('3-4', 3), '4_5'=> array('4-5', 4),
            '5_6'=> array('5-6', 5), '6_7'=> array('6-7', 6), 'other'=> array('other', 7)];

        $classes = [];
        $classesCapacity = [];
        $languages = [];
        $gradeTempArray = ['0_1'=> 0, '1_2'=> 0, '2_3'=> 0, '3_4'=> 0, '4_5'=> 0, '5_6'=> 0, '6_7'=> 0];

        foreach ($studentByGrades as $key => $studentByGrade) {
            $gradeGender = $studentByGrade->user->gender->name;
            $gradeTotal = $studentByGrade->total;
            $age = $studentByGrade->age;
            $disablity_count = $studentByGrade->disablity_count;

            $institutionClassesLanguage_id = $studentByGrade->InstitutionClasses['language_id'];
            $capacity = $studentByGrade->InstitutionClasses['capacity'];
            $institution_class_id = $studentByGrade->InstitutionClassStudents['institution_class_id'];
            $international_code = $studentByGrade->Languages['international_code'];

            if(empty($institutionClassesLanguage_id)){
                $institutionClassesLanguage_id = 'other';
                $international_code = 'other';
            }

            $form_of_training_name = $studentByGrade->education_grade->education_programme->education_form_of_training->code;

            $classes[$institutionClassesLanguage_id][$institution_class_id] += $gradeTotal;
            $classesCapacity[$institutionClassesLanguage_id][$institution_class_id] += $capacity;
            $languages[$institutionClassesLanguage_id] = $international_code;

            $i = 0;
            $is_find = false;
            foreach($gradeTempArray as $keyGrade=>$itemGrade){
                if($i == $age){
                    $dataSet[$gradeGender][$form_of_training_name]['data'][$keyGrade] += $gradeTotal;
                    $dataSet[$gradeGender][$form_of_training_name]['data_disablity'][$keyGrade] += $disablity_count;
                    $is_find = true;
                    break;
                }
                $i ++;
            }
            if(!$is_find){
                $dataSet[$gradeGender][$form_of_training_name]['data']['other'] += $gradeTotal;
                $dataSet[$gradeGender][$form_of_training_name]['data_disablity']['other'] += $disablity_count;
            }
        }
        $dataSet['Education_Grades'] = $grades;
        $dataSet['Classes'] = $classes;
        $dataSet['Classes_Capacity'] = $classesCapacity;
        $dataSet['Languages'] = $languages;
        $dataSet['Arrived'] = $arrived;
    }


    function getTotalStudentData($InstitutionStudents, $studentsByGradeConditions, &$dataSet){
        $query = $InstitutionStudents->find();
        $studentByGrades = $query
            ->select([
                $InstitutionStudents->aliasField('institution_id'),
                'Users.id',
                'Users.foreigner',
                'Genders.name',
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'Users.Genders'
            ])
            ->where($studentsByGradeConditions)
            ->group([
                'Users.foreigner',
                'Genders.name'
            ])
            ->toArray()
        ;

        foreach ($studentByGrades as $key => $studentByGrade){
            $gradeTotal = $studentByGrade->total;
            $is_foreigner = $studentByGrade->user->foreigner;
            $gradeGender = $studentByGrade->user->gender->name;

            if($is_foreigner)
                $dataSet['Foreigner_Total'][$gradeGender] += $gradeTotal;
            $dataSet['Total'][$gradeGender] += $gradeTotal;
        }
    }


    function getDataStudentsDoo(){
        $institutionId = 2367;
        $currentAcademPeriodId = 31;
        $InstitutionStudents = TableRegistry::get('Institution.Students');
        $EducationFormOfTraining = TableRegistry::get('Education.EducationFormOfTraining');
        $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
        $InstitutionClassStudents = TableRegistry::get('Institution.InstitutionClassStudents');
        $Users = TableRegistry::get('User.Users');
        $statuses = $StudentStatuses->findCodeList();

        $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $InstitutionClassStudents, $statuses, $institutionId, $currentAcademPeriodId);

        $query = $InstitutionStudents->find();
        $studentByGrades = $query
            ->select([
                $InstitutionStudents->aliasField('institution_id'),
                $InstitutionStudents->aliasField('education_grade_id'),
                'EducationGrades.name',
                'age' => $query->newExpr('TIMESTAMPDIFF( YEAR, Users.date_of_birth, CURDATE( ) )'),
                'Users.id',
                'Users.foreigner',
                'group_con' => 'group_concat(InstitutionClassStudents.institution_class_id)',
                'group_acad' => 'group_concat(InstitutionClassStudents.academic_period_id)',
                'InstitutionClassStudents.institution_class_id',
                'InstitutionClasses.name',
                'InstitutionClasses.language_id',
                'InstitutionClasses.capacity',
                'Languages.international_code',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id',
                'EducationFormOfTraining.code',
                'disablity_count' => $query->func()->count("DISTINCT `UserPeopleDisabilities`.`security_user_id`"),
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'EducationGrades.EducationStages',
                'EducationGrades.EducationProgrammes.EducationCycles.EducationLevels',
                'Users.Genders',
                'EducationGrades.EducationProgrammes.EducationFormOfTraining'
            ])
            ->join(['table'=> 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
            ->join(['table'=> 'institution_class_students', 'alias' => 'InstitutionClassStudents', 'type' => 'LEFT', 'conditions' => 'InstitutionClassStudents.student_id = Students.student_id'])
            ->join(['table'=> 'institution_classes', 'alias' => 'InstitutionClasses', 'type' => 'LEFT', 'conditions' => 'InstitutionClasses.id = InstitutionClassStudents.institution_class_id'])
            ->join(['table'=> 'languages', 'alias' => 'Languages', 'type' => 'LEFT', 'conditions' => 'Languages.id = InstitutionClasses.language_id'])
            ->where($studentsByGradeConditions)
            ->group([
                'Users.foreigner',
                'EducationGrades.education_stage_id',
                //'InstitutionClassStudents.institution_class_id',
                'Genders.name',
                'EducationProgrammes.education_form_of_training_id'
            ])
            ->toArray()
        ;
        //print_r($studentByGrades);die;
        $grades = ['0_1'=> array('0-2', 1), '1_2'=> array('0-2', 1), '2_3'=> array('2-3', 2), '3_4'=> array('3-4', 3), '4_5'=> array('4-5', 4),
            '5_6'=> array('5-6', 5), '6_7'=> array('6-7', 6), 'other'=> array('other', 7)];

        $classes = [];
        $classesCapacity = [];
        $languages = [];
        $genderOptions = $Users->Genders->getList();
        $formOfTrainingOptions = $EducationFormOfTraining->getListWithCode();
        $dataSet = array();

        $gradeArray = ['0_1'=> 0, '1_2'=> 0, '2_3'=> 0, '3_4'=> 0, '4_5'=> 0, '5_6'=> 0, '6_7'=> 0, 'other' => 0];
        $gradeTempArray = ['0_1'=> 0, '1_2'=> 0, '2_3'=> 0, '3_4'=> 0, '4_5'=> 0, '5_6'=> 0, '6_7'=> 0];


        foreach ($genderOptions as $key => $value) {
            foreach ($formOfTrainingOptions as $keyF => $valueF){
                $dataSet[$value][$valueF] = array('name' => $value, 'data_disablity' => $gradeArray, 'data' => $gradeArray);
            }
        }
        $dataSet['Foreigner_Total'] = ['Male' => 0, 'Female' => 0];
        $dataSet['Total'] = ['name' => 'Total', 'data_disablity' => $gradeArray, 'data' => $gradeArray];

        foreach ($studentByGrades as $key => $studentByGrade) {
            $gradeGender = $studentByGrade->user->gender->name;
            $gradeTotal = $studentByGrade->total;
            $age = $studentByGrade->age;
            $disablity_count = $studentByGrade->disablity_count;
            $is_foreigner = $studentByGrade->user->foreigner;

            $institutionClassesLanguage_id = $studentByGrade->InstitutionClasses['language_id'];
            $capacity = $studentByGrade->InstitutionClasses['capacity'];
            $institution_class_id = $studentByGrade->InstitutionClassStudents['institution_class_id'];
            $international_code = $studentByGrade->Languages['international_code'];
            $form_of_training_name = $studentByGrade->education_grade->education_programme->education_form_of_training->code;

            $classes[$institutionClassesLanguage_id][$institution_class_id] += $gradeTotal;
            $classesCapacity[$institutionClassesLanguage_id][$institution_class_id] += $capacity;
            $languages[$institutionClassesLanguage_id] = $international_code;

            $i = 0;
            $is_find = false;
            foreach($gradeTempArray as $keyGrade=>$itemGrade){
                if($i == $age){
                    $dataSet[$gradeGender][$form_of_training_name]['data'][$keyGrade] += $gradeTotal;
                    $dataSet[$gradeGender][$form_of_training_name]['data_disablity'][$keyGrade] += $disablity_count;
                    if($is_foreigner)
                        $dataSet['Foreigner_Total'][$gradeGender] += $gradeTotal;
                    $dataSet['Total']['data'][$keyGrade] += $gradeTotal;
                    $dataSet['Total']['data_disablity'][$keyGrade] += $disablity_count;
                    $is_find = true;
                    break;
                }
                $i ++;
            }
            if(!$is_find){
                $dataSet[$gradeGender][$form_of_training_name]['data']['other'] += $gradeTotal;
                $dataSet[$gradeGender][$form_of_training_name]['data_disablity']['other'] += $disablity_count;
                if($is_foreigner)
                    $dataSet['Foreigner_Total'][$gradeGender] += $gradeTotal;
                $dataSet['Total']['data']['other'] += $gradeTotal;
                $dataSet['Total']['data_disablity']['other'] += $disablity_count;
            }
        }
        $dataSet['Education_Grades'] = $grades;
        $dataSet['Classes'] = $classes;
        $dataSet['Classes_Capacity'] = $classesCapacity;
        $dataSet['Languages'] = $languages;

        print_r($dataSet);die;
    }


    function getConditionsByStudent($InstitutionStudents, $statusId, $institutionId, $currentAcademPeriodId){
        $params = [
            'conditions' => ['institution_id' => $institutionId, 'student_status_id' => $statusId]
        ];
        $conditions = isset($params['conditions']) ? $params['conditions'] : [];
        $_conditions = [];
        foreach ($conditions as $key => $value) {
            $_conditions[$InstitutionStudents->alias().'.'.$key] = $value;
        }
        $studentsByGradeConditions = [
            $InstitutionStudents->aliasField('academic_period_id') => $currentAcademPeriodId,
            'Genders.name IS NOT NULL'
        ];
        $studentsByGradeConditions = array_merge($studentsByGradeConditions, $_conditions);
        return $studentsByGradeConditions;
    }

    function getEntranceTotalByCode($international_code, $dataEntrance){
        if(count($dataEntrance) > 0) {
            foreach ($dataEntrance as $key => $item) {
                $international_code_entrance = $item['international_code'];
                if ($international_code == $international_code_entrance) {
                    return $item['total'];
                }
            }
        }
        return false;
    }




    function updateStudentFieldsStaff(){
        $studentCustomTable = TableRegistry::get('staff_custom_field_values');
        $this->loadModel('User.Users');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['staff_custom_field_id'=>7])
            ->toArray();

        /*foreach($studentArray as $key=>$item){
            $student_id = $item->staff_id;
            $number_value = $item->number_value;
            $correctNumberValue = 0;
            if($number_value == 136){
                $correctNumberValue = 1;
            }
            if($correctNumberValue) {
                $userItem = $this->Users
                    ->find()
                    ->where(['id' => $student_id])
                    ->first();
                $userItem->certificate_inclusive_education = $correctNumberValue;
                $this->Users->save($userItem);
            }
        }*/
        echo 'ok';die;
    }


    function updateStudentFields(){
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $this->loadModel('User.Users');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>33])
            ->toArray();

        /*foreach($studentArray as $key=>$item){
            $student_id = $item->student_id;
            $number_value = $item->number_value;
            $correctNumberValue = 0;
            if($number_value == 29){
                $correctNumberValue = 1;
            }
            if($correctNumberValue) {
                $userItem = $this->Users
                    ->find()
                    ->where(['id' => $student_id])
                    ->first();
                $userItem->sop = $correctNumberValue;
                $this->Users->save($userItem);
            }
        }*/
        echo 'ok';die;
    }

    function updateCustomFieldsInstitution50(){
        /*$this->loadModel('Institution.Institutions');
        $institutionCustomTable = TableRegistry::get('institution_custom_field_values');
        $institutionArray = $institutionCustomTable
            ->find()
            ->where(['institution_custom_field_id'=>30])
            ->toArray();
        foreach($institutionArray as $key=>$item){
            $institution_id = $item->institution_id;
            $number_value = $item->number_value;

            $query = $this->Institutions->query();
            $result = $query->update()
                ->set(['number_of_laboratories' => $number_value])
                ->where(['id' => $institution_id])
                ->execute();
        }
        echo 'ok';die;*/
    }

    function updateCustomFieldsInstitution5(){
        /*$institutionCustomTable = TableRegistry::get('institution_custom_field_values');
        $this->loadModel('Institution.Partners');
        $institutionArray = $institutionCustomTable
            ->find()
            ->where(['institution_custom_field_id'=>37])
            ->toArray();
        foreach($institutionArray as $key=>$item){
            $institution_id = $item->institution_id;
            $text_value = $item->text_value;

            $number_of_existing_contracts = '';
            $of_them_state_institutions = '';
            $private = '';

            $item1 = $institutionCustomTable
                ->find()
                ->where(['institution_custom_field_id'=>33, 'institution_id'=>$institution_id])
                ->first();
            if(count($item1) > 0){
                $number_of_existing_contracts = $item1->number_value;
            }

            $item2 = $institutionCustomTable
                ->find()
                ->where(['institution_custom_field_id'=>34, 'institution_id'=>$institution_id])
                ->first();
            if(count($item2) > 0){
                $of_them_state_institutions = $item2->number_value;
            }

            $item3 = $institutionCustomTable
                ->find()
                ->where(['institution_custom_field_id'=>35, 'institution_id'=>$institution_id])
                ->first();
            if(count($item3) > 0){
                $private = $item3->number_value;
            }

            $PartnersItem = $this->Partners
                ->find()
                ->where(['institution_id'=>$institution_id])
                ->first();
            if(count($PartnersItem) == 0){
                $data = array();
                $data['number_of_existing_contracts'] = $number_of_existing_contracts;
                $data['of_them_state_institutions'] = $of_them_state_institutions;
                $data['private'] = $private;
                $data['partners_concluded_cooperation_agreements'] = $text_value;

                $data['institution_id'] = $institution_id;

                $Staff = $this->Partners->newEntity($data);
                $this->Partners->save($Staff);
            }
        }

        echo 'ok';die;*/
    }

    function updateCustomFieldsInstitution2(){
        /*$this->loadModel('Institution.Institutions');
        $institutionCustomTable = TableRegistry::get('institution_custom_field_values');

        $institutionArray = $institutionCustomTable
            ->find()
            ->where(['institution_custom_field_id'=>39])
            ->toArray();

        foreach($institutionArray as $key=>$item){
            $institution_id = $item->institution_id;
            $number_value = $item->number_value;

            $correctNumberValue = 0;
            if($number_value == 66){
                $correctNumberValue = 1;
            }

            $query = $this->Institutions->query();
            $result = $query->update()
                ->set(['board_of_trustee' => $correctNumberValue])
                ->where(['id' => $institution_id])
                ->execute();
        }
        echo 'ok';die;*/
    }

    function updateCustomFieldsInstitution3(){
        /*$this->loadModel('Institution.Institutions');
        $this->loadModel('Institution.InfrastructureUtilityInternets');
        $this->loadModel('AcademicPeriod.AcademicPeriods');
        $institutionCustomTable = TableRegistry::get('institution_custom_field_values');
        $currentAcademPeriod = $this->AcademicPeriods->getCurrent();

        $institutionArray = $institutionCustomTable
            ->find()
            ->where(['institution_custom_field_id'=>25])
            ->toArray();

        foreach($institutionArray as $key=>$item){
            $institution_id = $item->institution_id;
            $number_value = $item->number_value;

            $correctNumberValue = 0;
            if($number_value == 49){
                $correctNumberValue = 1;
            }

            if($correctNumberValue){
                $monMtbItem = $this->InfrastructureUtilityInternets
                    ->find()
                    ->where(['institution_id'=>$institution_id])
                    ->first();
                if(count($monMtbItem) == 0){
                    $data = array();
                    $data['internet_availability'] = 0;
                    $data['internet_provider_id'] = 3;
                    $data['utility_internet_type_id'] = 1;
                    $data['utility_internet_condition_id'] = 1;
                    $data['internet_purpose'] = 1;

                    $data['institution_id'] = $institution_id;
                    $data['academic_period_id'] = $currentAcademPeriod;

                    $Staff = $this->InfrastructureUtilityInternets->newEntity($data);
                    $this->InfrastructureUtilityInternets->save($Staff);
                }
            }
        }
        echo 'ok';die;*/
    }

    function updateInstitutionDooPurpose(){
        /*$this->loadModel('Institution.Institutions');
        $institutionArray = $this->Institutions
            ->find()
            ->where(['institution_type_id'=>1])
            ->toArray();
        foreach($institutionArray as $institutionItem){
            $institutionItem->doo_duration = 2;
            $this->Institutions->save($institutionItem);
        }*/

        die;
    }

    function updateCustomFieldsInstitution(){

        /*$this->loadModel('Institution.Institutions');
        $this->loadModel('AcademicPeriod.AcademicPeriods');
        $this->loadModel('Institution.MonMtb');
        $institutionCustomTable = TableRegistry::get('institution_custom_field_values');

        $currentAcademPeriod = $this->AcademicPeriods->getCurrent();

        $institutionArray = $institutionCustomTable
            ->find()
            ->where(['institution_custom_field_id'=>28])
            ->toArray();

        foreach($institutionArray as $key=>$item){
            $institution_id = $item->institution_id;
            $number_value = $item->number_value;

            $monMtbItem = $this->MonMtb
                ->find()
                ->where(['institution_id'=>$institution_id])
                ->first();

            if(count($monMtbItem) == 0){
                $data = array();
                $data['number_of_computer_classes'] = $number_value;

                $number_of_computers = '';
                $institutionArray2 = $institutionCustomTable
                    ->find()
                    ->where(['institution_custom_field_id'=>1, 'institution_id'=>$institution_id])
                    ->toArray();
                if(count($institutionArray2) > 0){
                    $number_of_computers = $institutionArray2[0]->number_value;
                }

                $number_of_computers_connected_to_the_internet = '';
                $institutionArray3 = $institutionCustomTable
                    ->find()
                    ->where(['institution_custom_field_id'=>26, 'institution_id'=>$institution_id])
                    ->toArray();
                if(count($institutionArray3) > 0){
                    $number_of_computers_connected_to_the_internet = $institutionArray3[0]->number_value;
                }

                $data['number_of_computers'] = $number_of_computers;
                $data['number_of_computers_connected_to_the_internet'] = $number_of_computers_connected_to_the_internet;

                $data['institution_id'] = $institution_id;
                $data['academic_period_id'] = $currentAcademPeriod;

                $Staff = $this->MonMtb->newEntity($data);
                $this->MonMtb->save($Staff);
            }
        }
        echo 'ok';die;*/
    }

    function updateNationStaff(){
//        $classesTable = TableRegistry::get('institution_classes');
//        $classes = $classesTable
//            ->find()
//            ->where(['language'=>5])
//            ->all();
//        $i=0;
//        foreach($classes as $class){
//            $class->language_id = 461;
//            $classesTable->save($class);
//        }
        /*foreach($studentArray as $key=>$item){
            $student_id = $item->staff_id;
            $nation_id = $item->number_value;
            $correctNationId = $this->getCorrectNationId($nation_id);
            if($correctNationId){
                $userItem = $this->Users
                    ->find()
                    ->where(['id'=>$student_id])
                    ->first();
                $userItem->nationality_user_id = $correctNationId;
                $this->Users->save($userItem);
            }
        }*/
        die;
    }


    function updateFormOfStudy(){
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $this->loadModel('User.Users');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>40])
            ->toArray();

        /*foreach($studentArray as $key=>$item){
            $student_id = $item->student_id;
            $nation_id = $item->number_value;
            $correctNationId = $this->getCorrectFormOfStudy($nation_id);
            if($correctNationId){
                $userItem = $this->Users
                    ->find()
                    ->where(['id'=>$student_id])
                    ->first();
                $userItem->form_of_study_id = $correctNationId;
                $this->Users->save($userItem);
            }
        }*/
        die;
    }

    function getCorrectFormOfStudy($nation_id){
        switch ($nation_id) {
            case 150://очная
                return 1;
            case 151://заочная
                return 2;
            case 152://краткосроч
                return 3;
            case 153://вечерняя
                return 4;
            case 154://дистанц
                return 5;
            case 155://бюджетная
                return 6;
            case 156://контрактная
                return 7;
        }
        return false;
    }



    function updateNation(){
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $this->loadModel('User.Users');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>28])
            ->toArray();

        /*foreach($studentArray as $key=>$item){
            $student_id = $item->student_id;
            $nation_id = $item->number_value;
            $correctNationId = $this->getCorrectNationId($nation_id);
            if($correctNationId){
                $userItem = $this->Users
                    ->find()
                    ->where(['id'=>$student_id])
                    ->first();
                $userItem->nationality_user_id = $correctNationId;
                $this->Users->save($userItem);
            }
        }*/
        die;
    }


    function getCorrectNationId($nation_id){
        switch ($nation_id) {
            case 1://Кыргыз(ка)
                return 1;
            case 3://Узбек(чка)
                return 119;
            case 9://Таджик(чка)
                return 104;
            case 2://Русский(ая)
                return 97;
            case 21://Дунганин(ка)
                return 41;
            case 20://Уйгур(ка)
                return 120;
            case 124://Курды
                return 66;
            case 12://Чеченец(ка)
                return 135;
            case 8://Азербайджанец(ка)
                return 7;
            case 47://Калмыки
                return 52;
            case 10://Немец(ка)
                return 85;
            case 6://Казах(шка)
                return 51;
            case 4://Украинец(ка)
                return 121;
            case 13://Чеченец(ка)
                return 135;
            case 22://Кореец(янка)
                return 61;
            case 15://Молдаванин(ка)
                return 79;
            case 139://Другие национальности
                return 155;
            case 5://Татарин(ка)
                return 106;
            case 40://Лезгины
                return 73;
            case 23://Турок(чанка)
                return 114;
            case 136://Хорваты
                return 129;
            case 93://Гагаузы
                return 31;
            case 17://Туркмен(ка)
                return 117;
            case 90://Юкагиры
                return 152;
            case 81://Саамы
                return 99;
            case 19://еврейка
                return 42;
            case 48://Каракалпаки
                return 56;
            case 7://Грузин
                return 37;
            case 121://Китайцы
                return 60;
            case 161://Иранец ...
                return 155;
            case 37://Даргинцы
                return 38;
            case 11://Армянин
                return 15;
            case 31://Балкарцы
                return 20;
            case 32://Башкиры
                return 21;
            case 14://Литовец
                return 74;
            case 116://Греки
                return 36;
            case 118://Народы Индии и Пакистана
                return 93;
            case 60://Карачаевцы
                return 53;
            case 63://Коми-пермяки
                return 64;
            case 18://Эстонец
                return 148;
            case 103://Цыгане
                return 131;
            case 41://Ногайцы
                return 88;
            case 138://Японцы
                return 154;
            case 108://Арабы
                return 14;
            case 53://Осетины
                return 91;
            case 126://Персы
                return 94;
            case 57://Якуты
                return 153;
            case 106://Американцы
                return 11;
            case 109://Ассирийцы
                return 17;
            case 100://Татары крымские
                return 107;
            case 133://Финн
                return 150;
            case 110://Афганцы
                return 18;
            case 56://Чуваши
                return 137;
            case 66://Коряки
                return 62;
            case 52://Мордва
                return 78;
            case 34://Hapoдности Дагестана
                return 155;
            case 38://Кумыки
                return 70;
            case 64://......
                return 155;
            case 68://Ненцы
                return 86;
            case 42://Рутуйцы
                return 96;
            case 127://Поляки
                return 92;
            case 104://Шорцы
                return 143;
            case 51://Марийцы
                return 77;
            case 55://Удмypты
                return 118;
            case 61://Хакасы
                return 125;
            case 39://Лакцы
                return 71;
            case 112://Болгары
                return 26;
            case 16://Латыш(ка)
                return 72;
            case 50://Коми
                return 63;
            case 36://Агулы
                return 5;
        }
        return false;
    }

    function createParentFather(){
        $this->loadModel('Student.StudentGuardians');
        $this->loadModel('User.Users');
        $this->loadComponent('Institution.CreateUsers');
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>11])
            ->toArray();


        $count_iteration = 1;
        //print_r($studentArray);die;
        if(count($studentArray) > 0){
            for($i = 151813; $i < count($studentArray); $i++){
                if($count_iteration == 7000) break;
                $student_id = $studentArray[$i]->student_id;
                $motherLastName = $studentArray[$i]->text_value;


                $guardArray = $this->StudentGuardians
                    ->find()
                    ->where(['student_id'=>$student_id, 'guardian_relation_id'=>648])
                    ->toArray();

                //echo count($guardArray);die;
                if(count($guardArray) == 0) {
                    if ($motherLastName) {
                        $studentArray2 = $studentCustomTable
                            ->find()
                            ->where(['student_custom_field_id' => 10, 'student_id' => $student_id])
                            ->toArray();
                        if (count($studentArray2) > 0) {

                            $motherFirstName = $studentArray2[0]->text_value;

                            $openemisJson = json_decode($this->CreateUsers->getUniqueOpenemisId());
                            $openemisId = $openemisJson->openemis_no;

                            $data = array();
                            $data['first_name'] = $motherFirstName;
                            $data['last_name'] = $motherLastName;
                            $data['date_of_birth'] = '1990-02-02';

                            $data['username'] = $openemisId;
                            $data['openemis_no'] = $openemisId;
                            $data['password'] = $openemisId;


                            $data['gender_id'] = 1;
                            $data['is_guardian'] = 1;
                            $data['super_admin'] = '0';
                            $data['status'] = '1';

                            $Staff = $this->Users->newEntity($data);
                            $this->Users->save($Staff);

                            $mother_id = $Staff->id;


                            if ($mother_id) {
                                $data2 = array();
                                $data2['student_id'] = $student_id;
                                $data2['guardian_id'] = $mother_id;
                                $data2['guardian_relation_id'] = 648;

                                $StudentGuardiansItem = $this->StudentGuardians->newEntity($data2);
                                $this->StudentGuardians->save($StudentGuardiansItem);
                            }
                        }
                    }
                }
                $count_iteration ++;
                echo $i . '/' . $student_id .'<br>';
            }
        }
        echo 'ok';die;
    }

    function createPerents(){

        $this->loadModel('Student.StudentGuardians');
        $this->loadModel('User.Users');
        $this->loadComponent('Institution.CreateUsers');
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>2])
            ->toArray();


        $count_iteration = 1;
        //print_r($studentArray[31160]);die;
        if(count($studentArray) > 0){
            for($i = 167741; $i < count($studentArray); $i++){
                if($count_iteration == 7000) break;
                $student_id = $studentArray[$i]->student_id;
                $motherLastName = $studentArray[$i]->text_value;


                $guardArray = $this->StudentGuardians
                    ->find()
                    ->where(['student_id'=>$student_id, 'guardian_relation_id'=>647])
                    ->toArray();

                //echo count($guardArray);die;
                if(count($guardArray) == 0) {
                    if ($motherLastName) {
                        $studentArray2 = $studentCustomTable
                            ->find()
                            ->where(['student_custom_field_id' => 1, 'student_id' => $student_id])
                            ->toArray();
                        if (count($studentArray2) > 0) {

                            $motherFirstName = $studentArray2[0]->text_value;

                            $openemisJson = json_decode($this->CreateUsers->getUniqueOpenemisId());
                            $openemisId = $openemisJson->openemis_no;

                            $data = array();
                            $data['first_name'] = $motherFirstName;
                            $data['last_name'] = $motherLastName;
                            $data['date_of_birth'] = '1990-02-02';

                            $data['username'] = $openemisId;
                            $data['openemis_no'] = $openemisId;
                            $data['password'] = $openemisId;


                            $data['gender_id'] = 2;
                            $data['is_guardian'] = 1;
                            $data['super_admin'] = '0';
                            $data['status'] = '1';

                            $Staff = $this->Users->newEntity($data);
                            $this->Users->save($Staff);

                            $mother_id = $Staff->id;


                            if ($mother_id) {
                                $data2 = array();
                                $data2['student_id'] = $student_id;
                                $data2['guardian_id'] = $mother_id;
                                $data2['guardian_relation_id'] = 647;

                                $StudentGuardiansItem = $this->StudentGuardians->newEntity($data2);
                                $this->StudentGuardians->save($StudentGuardiansItem);
                            }
                        }
                    }
                }
                $count_iteration ++;
                echo $i . '/' . $student_id .'<br>';
            }
        }
        echo 'ok';die;
    }


    /*function createPractice(){
        $today = new DateTime();
        $startDate = $today->format('Y-m-d H:i:s');
        $this->loadModel('StudentPractice');
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>41])
            ->toArray();

        if(count($studentArray) > 0){
            foreach($studentArray as $key=>$item){
                $student_id = $item->student_id;
                $practiceName = $item->text_value;
                $ptacticeAction = $this->getPracticeAction($student_id);

                //echo $ptacticeAction;die;
                if($ptacticeAction) {
                    $newArr = [
                        'place_of_practice' => $practiceName,
                        'type_of_practice_id' => $ptacticeAction,
                        'academic_period_id' => 30,
                        'student_id' => $student_id,
                        'created_user_id' => 101822,
                        'created' => $startDate
                    ];
                    $this->StudentPractice->save($this->StudentPractice->newEntity($newArr));
                }
            }
        }
        echo 'ok';die;
    }*/

    function getPracticeAction($student_id){
        $studentCustomTable = TableRegistry::get('student_custom_field_values');
        $studentArray = $studentCustomTable
            ->find()
            ->where(['student_custom_field_id'=>38, 'student_id'=>$student_id])
            ->toArray();
        if(count($studentArray) > 0){
            return 1;
        }else{
            $studentArray2 = $studentCustomTable
                ->find()
                ->where(['student_custom_field_id'=>39, 'student_id'=>$student_id])
                ->toArray();
            if(count($studentArray) > 0){
                return 2;
            }
        }
        return false;
    }

    /*function gdaza(){
        print_r(phpinfo());die;
    }*/

    /*function deleteEducationProgramme(){
        $this->loadModel('Education.EducationProgrammes');
        $this->loadModel('Education.EducationCycles');
        $this->loadModel('Education.EducationGrades');
        $this->loadModel('Education.EducationGradesSubjects');
        $this->loadModel('Institution.InstitutionGrades');

        $joinCycles = [
            'table' => 'education_cycles',
            'alias' => 'EducationCycles',
            'conditions' => [
                'EducationCycles.id = EducationProgrammes.education_cycle_id'
            ]
        ];
        $condition = [$this->EducationCycles->aliasField('id')=> 41];
        $educationProgrammesArray = $this->EducationProgrammes->find()
            ->join([$joinCycles])
            ->where([
                $condition
            ])
            ->toArray();

        foreach($educationProgrammesArray as $keyProgramme=>$itemProgramme){
            $is_find = false;
            $programmeId = $itemProgramme->id;
            $educationGradesArray = $this->EducationGrades->find()
                ->where(['education_programme_id'=>$programmeId])
                ->toArray();
            if(count($educationGradesArray) > 0){
                foreach($educationGradesArray as $keyGrade=>$itemGrade){
                    $gradeId = $itemGrade->id;
                    $institutionGradesArray = $this->InstitutionGrades->find()
                        ->where(['education_grade_id'=>$gradeId])
                        ->toArray();
                    if(count($institutionGradesArray) > 0){
                        $is_find = true;
                        break;
                    }
                }
            }
            if(!$is_find){

                $this->EducationProgrammes->delete($itemProgramme, array());
                foreach($educationGradesArray as $keyGrade=>$itemGrade){
                    $gradeId = $itemGrade->id;
                    $this->EducationGrades->delete($itemGrade, array());
                    $educationGradesSubjectsArray = $this->EducationGradesSubjects->find()
                        ->where(['education_grade_id'=>$gradeId])
                        ->toArray();
                    if(count($educationGradesSubjectsArray) > 0){
                        foreach($educationGradesSubjectsArray as $keyGradeSubject=>$itemGradeSubject){
                            $this->EducationGradesSubjects->delete($itemGradeSubject, array());
                        }
                    }
                }
            }
        }
        die;
    }*/


    /*function ddd(){
        $associations = TableRegistry::get('Institution.Types')->associations();
        foreach ($associations as $assoc){
            echo $assoc->registryAlias() . '/' . $assoc->foreignKey() . '<br>';
        }
        die;
            print_r($associations);die;
        //$this->loadModel('Institution.Types');
        //echo $this->Institutions->registryAlias();die;
    }*/



    public function onRenderBinary(Event $event, Entity $entity, PageElement $element)
    {
        $attributes = $element->getAttributes();
        $type = isset($attributes['type']) ? $attributes['type'] : 'binary';
        $fileNameField = isset($attributes['fileNameField']) ? $attributes['fileNameField'] : 'file_name';
        $fileContentField = $element->getKey();
        if ($type == 'image') {
            if ($this->request->param('_ext') == 'json') {
                $primaryKey = $entity->primaryKey;
                $source = isset($attributes['source']) ? $attributes['source'] : $entity->source();
                if (isset($attributes['keyField'])) {
                    $key = TableRegistry::get($source)->primaryKey();
                    if (!is_array($key)) {
                        $primaryKey = $this->encode([$key => $entity->{$attributes['keyField']}]);
                    }
                }
                if ($entity->{$fileContentField}) {
                    return Router::url([
                        'plugin' => null,
                        '_method' => 'GET',
                        'version' => 'v2',
                        'model' => $source,
                        'controller' => 'Restful',
                        'action' => 'image',
                        'id' => $primaryKey,
                        'fileName' => $fileNameField,
                        'fileContent' => $fileContentField,
                        '_ext' => 'json'
                    ], true);
                }
            } else {
                switch ($this->request->param('action')) {
                    case 'view':
                        $fileName = $entity->{$fileNameField};
                        $pathInfo = pathinfo($fileName);
                        if ($entity->{$fileContentField}) {
                            $file = stream_get_contents($entity->{$fileContentField});
                            rewind($entity->{$fileContentField});
                            $entity->{$fileNameField} = 'data:'.$this->response->getMimeType($pathInfo['extension']).';base64,'. base64_encode($file);
                            return $entity->{$fileNameField};
                        }
                        break;
                    case 'index':
                        $primaryKey = $entity->primaryKey;
                        $source = isset($attributes['source']) ? $attributes['source'] : $entity->source();
                        if (isset($attributes['keyField'])) {
                            $key = TableRegistry::get($source)->primaryKey();
                            if (!is_array($key)) {
                                $primaryKey = $this->encode([$key => $entity->{$attributes['keyField']}]);
                            }
                        }
                        if ($entity->{$fileContentField}) {
                            return Router::url([
                                'plugin' => null,
                                '_method' => 'GET',
                                'version' => 'v2',
                                'model' => $source,
                                'controller' => 'Restful',
                                'action' => 'image',
                                'id' => $primaryKey,
                                'fileName' => $fileNameField,
                                'fileContent' => $fileContentField,
                                '_ext' => 'json'
                            ], true);
                        }
                        break;
                    case 'edit':
                    case 'delete':
                        $fileName = $entity->{$fileNameField};
                        $pathInfo = pathinfo($fileName);
                        if ($entity->{$fileContentField}) {
                            if (is_resource($entity->{$fileContentField})) {
                                $file = stream_get_contents($entity->{$fileContentField});
                            } else {
                                $file = $entity->{$fileContentField};
                            }

                            $returnValue = [
                                'extension' => $pathInfo['extension'],
                                'filename' => $fileName,
                                'src' => 'data:'.$this->response->getMimeType($pathInfo['extension']).';base64,'. base64_encode($file)
                            ];

                            rewind($entity->{$fileContentField});
                            return $returnValue;
                        }
                        break;
                }
            }
        } else {
            switch ($this->request->param('action')) {
                case 'view':
                    $primaryKey = $entity->primaryKey;
                    $source = isset($attributes['source']) ? $attributes['source'] : $entity->source();
                    if (isset($attributes['keyField'])) {
                        $key = TableRegistry::get($source)->primaryKey();
                        if (!is_array($key)) {
                            $primaryKey = $this->encode([$key => $entity->{$attributes['keyField']}]);
                        }
                    }
                    $fileName = $entity->{$fileNameField};
                    $element->setAttributes('file_name', $fileName);
                    if ($entity->{$fileContentField}) {
                        return Router::url([
                            'plugin' => null,
                            '_method' => 'GET',
                            'version' => 'v2',
                            'model' => $source,
                            'controller' => 'Restful',
                            'action' => 'download',
                            'id' => $primaryKey,
                            'fileName' => $fileNameField,
                            'fileContent' => $fileContentField,
                            '_ext' => 'json'
                        ], true);
                    }
                    break;
            }
        }
    }

    private function convertBase64ToBinary2(Entity $entity, $model)
    {
        $table = $model;
        $schema = $table->schema();
        $columns = $schema->columns();

        foreach ($columns as $column) {
            $attr = $schema->column($column);
            if ($attr['type'] == 'binary' && $entity->has($column)) {
                $value = urldecode($entity->$column);
                $entity->$column = base64_decode($value);
            }
        }
        return $entity;
    }


    private function initializeToolbars()
    {
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
            case 'index':
                if (!in_array('add', $disabledActions)) {
                    $page->addToolbar('add', [
                        'type' => 'element',
                        'element' => 'Page.button',
                        'data' => [
                            'title' => __('Add'),
                            'url' => ['action' => 'add'],
                            'iconClass' => 'fa kd-add ty',
                            'svgIcon' => '<svg class="addSvg" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M0 10C0 4.47715 4.47715 0 10 0H22C27.5228 0 32 4.47715 32 10V22C32 27.5228 27.5228 32 22 32H10C4.47715 32 0 27.5228 0 22V10Z" fill="#009966"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.1667 15.5H24.6667V17.1667H17.1667V24.6667H15.5V17.1667H8V15.5H15.5V8H17.1667V15.5Z" fill="white"/> </svg>',
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
                }

                break;
            case 'view':
                $primaryKey = !is_array($data) ? $data->primaryKey : $data['primaryKey']; // $data may be Entity or array

                $page->addToolbar('back', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('Back'),
                        'url' => ['action' => 'index'],
                        'urlParams' => 'QUERY',
                        'svgIcon' => '<svg class="backSvg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.404 11.1517H7.18845L14.1773 4.16285L12.402 2.39999L2.39999 12.402L12.402 22.404L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path></svg>',
                        'iconClass' => 'fa kd-back',
                        'linkOptions' => ['title' => __('Back'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);

                if (!in_array('edit', $disabledActions)) {
                    $page->addToolbar('edit', [
                        'type' => 'element',
                        'element' => 'Page.button',
                        'data' => [
                            'title' => __('Edit'),
                            'url' => ['action' => 'edit', $primaryKey],
                            'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M2.4 16.5601L0 24.0001L7.44 21.6001L2.4 16.5601Z" fill="#009966"></path><path d="M15.7952 3.12597L4.08569 14.8354L9.17678 19.9265L20.8863 8.21706L15.7952 3.12597Z" fill="#009966"></path><path d="M23.64 3.72L20.28 0.36C19.8 -0.12 19.08 -0.12 18.6 0.36L17.52 1.44L22.56 6.48L23.64 5.4C24.12 4.92 24.12 4.2 23.64 3.72Z" fill="#009966"></path></g><defs><clipPath id="clip0"><rect width="24" height="24" fill="white"></rect></clipPath></defs></svg>',
                            'iconClass' => 'fa kd-edit',
                            'linkOptions' => ['title' => __('Edit')]
                        ],
                        'options' => []
                    ]);
                }

                if (!in_array('delete', $disabledActions)) {
                    $page->addToolbar('remove', [
                        'type' => 'element',
                        'element' => 'Page.button',
                        'data' => [
                            'title' => __('Delete'),
                            'url' => ['action' => 'delete', $primaryKey],
                            'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21.3335C4 22.8069 5.19331 24.0002 6.66669 24.0002H17.3334C18.8067 24.0002 20 22.8069 20 21.3335V5.3335H4V21.3335Z" fill="#C71100"></path><path d="M16.6667 1.33331L15.3334 0H8.66675L7.33337 1.33331H2.66675V4H21.3334V1.33331H16.6667Z" fill="#C71100"></path></svg>',
                            'iconClass' => 'fa kd-trash',
                            'linkOptions' => ['title' => __('Delete')]
                        ],
                        'options' => []
                    ]);
                }
                break;
            case 'add':
                $page->addToolbar('back', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('Back'),
                        'url' => ['action' => 'index'],
                        'urlParams' => 'QUERY',
                        'svgIcon' => '<svg class="backSvg" width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.404 11.1517H7.18845L14.1773 4.16285L12.402 2.39999L2.39999 12.402L12.402 22.404L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path></svg>',
                        'iconClass' => 'fa kd-back',
                        'linkOptions' => ['title' => __('Back'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);
                break;
            case 'edit':
                $primaryKey = !is_array($data) ? $data->primaryKey : $data['primaryKey']; // $data may be Entity or array

                $page->addToolbar('view', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('View'),
                        'url' => ['action' => 'view', $primaryKey],
                        'svgIcon' => '<svg class="backSvg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.404 11.1517H7.18845L14.1773 4.16285L12.402 2.39999L2.39999 12.402L12.402 22.404L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path></svg>',

                        'iconClass' => 'fa kd-back',
                        'linkOptions' => ['title' => __('Back'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);

                $page->addToolbar('list', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('List'),
                        'url' => ['action' => 'index'],
                        'urlParams' => 'QUERY',
                        'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>',
                        'iconClass' => 'fa kd-lists',
                        'linkOptions' => ['title' => __('List')]
                    ],
                    'options' => []
                ]);
                break;
            case 'delete':
                $primaryKey = !is_array($data) ? $data->primaryKey : $data['primaryKey']; // $data may be Entity or array

                $page->addToolbar('view', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('Back'),
                        'url' => ['action' => 'view', $primaryKey],
                        'svgIcon' => '<svg class="backSvg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.404 11.1517H7.18845L14.1773 4.16285L12.402 2.39999L2.39999 12.402L12.402 22.404L14.1648 20.6411L7.18845 13.6522H22.404V11.1517Z" fill="#004A51"></path></svg>',
                        'iconClass' => 'fa kd-back',
                        'linkOptions' => ['title' => __('Back'), 'id' => 'btn-back']
                    ],
                    'options' => []
                ]);

                $page->addToolbar('list', [
                    'type' => 'element',
                    'element' => 'Page.button',
                    'data' => [
                        'title' => __('List'),
                        'url' => ['action' => 'index'],
                        'urlParams' => 'QUERY',
                        'svgIcon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2.75 6C2.75 5.17 3.42 4.5 4.25 4.5C5.08 4.5 5.75 5.17 5.75 6C5.75 6.83 5.08 7.5 4.25 7.5C3.42 7.5 2.75 6.83 2.75 6ZM2.75 12C2.75 11.17 3.42 10.5 4.25 10.5C5.08 10.5 5.75 11.17 5.75 12C5.75 12.83 5.08 13.5 4.25 13.5C3.42 13.5 2.75 12.83 2.75 12ZM4.25 16.5C3.42 16.5 2.75 17.18 2.75 18C2.75 18.82 3.43 19.5 4.25 19.5C5.07 19.5 5.75 18.82 5.75 18C5.75 17.18 5.08 16.5 4.25 16.5ZM21.25 19H7.25V17H21.25V19ZM7.25 13H21.25V11H7.25V13ZM7.25 7V5H21.25V7H7.25Z" fill="#FF6C6C"/> </svg>',
                        'iconClass' => 'fa kd-lists',
                        'linkOptions' => ['title' => __('List')]
                    ],
                    'options' => []
                ]);
                break;

            default:
                break;
        }
    }
}
