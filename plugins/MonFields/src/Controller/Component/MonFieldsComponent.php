<?php
namespace MonFields\Controller\Component;

use Cake\Console\ShellDispatcher;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Controller\Component;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Page\Model\Entity\PageElement;

class MonFieldsComponent extends Component
{
    const EXT = 'Table.php';
    const T_EXT = '.*\\' . self::EXT;
    const P_DIR = ROOT . '/plugins/';

    public $models = [
        'General' => [
            ['text' => 'Overview', 'value' => 'Institution.Institutions'],
            ['text' => 'Licensing', 'value' => 'Institution.MonLicensing'],
            ['text' => 'Calendar', 'value' => 'Calendars'],
            ['text' => 'Contacts', 'value' => 'Institution.InstitutionContacts'],
            ['text' => 'Contact Persons', 'value' => 'Institution.InstitutionContactPersons'],
            ['text' => 'Attachments', 'value' => 'Institution.InstitutionAttachments'],
            ['text' => 'AdditionallyReports', 'value' => 'Institution.InstitutionAdditionallyReports'],
            ['text' => 'AggregatedDataVpo', 'value' => 'Institution.InstitutionAggregatedDataVpo'],
            ['text' => 'AggregatedDataSpo', 'value' => 'Institution.InstitutionAggregatedDataSpo'],
            ['text' => 'AggregatedDataDoo', 'value' => 'Institution.InstitutionAggregatedDataDoo'],
            ['text' => 'InstitutionAttendance', 'value' => 'Institution.InstitutionAttendance'],
            ['text' => 'Correspondence', 'value' => 'Institution.Correspondence'],
        ],
        'Academic' => [
            ['text' => 'Shifts', 'value' => 'Institution.InstitutionShifts'],
            ['text' => 'Programmes', 'value' => 'Institution.InstitutionGrades'],
            ['text' => 'Classes', 'value' => 'Institution.InstitutionClasses'],
            ['text' => 'Subjects', 'value' => 'Institution.InstitutionSubjects'],
            ['text' => 'Timetables', 'value' => 'Schedule.ScheduleTimetables'],
            ['text' => 'Intervals', 'value' => 'Schedule.ScheduleIntervals'],
            ['text' => 'Terms', 'value' => 'Schedule.ScheduleTerms'],
            ['text' => 'Textbooks', 'value' => 'Institution.InstitutionTextbooks'],
            ['text' => 'Outgoing', 'value' => 'Institution.FeederOutgoingInstitutions'],
            ['text' => 'Incoming', 'value' => 'Institution.FeederIncomingInstitutions'],
            ['text' => 'Faculties', 'value' => 'Institution.InstitutionFaculties'],
        ],
        'User' => [
            //['text' => 'Users', 'value' => 'User.Users'],
            ['text' => 'Demographic', 'value' => 'User.Demographic'],
            ['text' => 'Identities', 'value' => 'User.Identities'],
            ['text' => 'Nationalities', 'value' => 'User.UserNationalities'],
            ['text' => 'Contacts', 'value' => 'User.Contacts'],
            ['text' => 'Languages', 'value' => 'User.UserLanguages'],
            ['text' => 'Attachments', 'value' => 'User.Attachments'],
            ['text' => 'Comments', 'value' => 'User.Comments'],
            ['text' => 'Awards', 'value' => 'User.Awards'],
            ['text' => 'Employments', 'value' => 'User.UserEmployments'],
            ['text' => 'Bank Accounts', 'value' => 'User.BankAccounts'],
        ],
        'Students' => [
            ['text' => 'Overview', 'value' => 'Institution.StudentUser'],
            ['text' => 'Account', 'value' => 'Institution.StudentAccount'],
            ['text' => 'Guardians', 'value' => 'Student.Guardians'],
            ['text' => 'Transport', 'value' => 'Student.StudentTransport'],
            ['text' => 'Programmes', 'value' => 'Student.Programmes'],
            ['text' => 'Classes', 'value' => 'Student.StudentClasses'],
            ['text' => 'Subjects', 'value' => 'Student.StudentSubjects'],
            ['text' => 'Absences', 'value' => 'Student.Absences'],
            ['text' => 'Behaviours', 'value' => 'Student.StudentBehaviours'],
            ['text' => 'Outcomes', 'value' => 'Student.StudentOutcomes'],
            ['text' => 'Competencies', 'value' => 'Institution.StudentCompetencies'],
            ['text' => 'Report Cards', 'value' => 'Student.StudentReportCards'],
            ['text' => 'Extracurriculars', 'value' => 'Student.Extracurriculars'],
            ['text' => 'Textbooks', 'value' => 'Student.Textbooks'],
            ['text' => 'Risks', 'value' => 'Student.StudentRisks'],
            ['text' => 'Counselling', 'value' => 'Counselling.Counsellings'],
            ['text' => 'Fees', 'value' => 'Student.StudentFees'],
            ['text' => 'Visit Requests', 'value' => 'Student.StudentVisitRequests'],
            ['text' => 'Visit Visits', 'value' => 'Student.StudentVisits'],
            ['text' => 'Practice', 'value' => 'Student.StudentPractice'],
            ['text' => 'Student Education', 'value' => 'Student.StudentEducation'],
            ['text' => 'Student Transfer Out', 'value' => 'Institution.StudentTransferOut'],
        ],
        'Staff' => [
            ['text' => 'Overview', 'value' => 'Institution.StaffUser'],
            ['text' => 'Account', 'value' => 'Institution.StaffAccount'],
            ['text' => 'Statuses', 'value' => 'Staff.EmploymentStatuses'],
            ['text' => 'Positions', 'value' => 'Staff.Positions'],
            ['text' => 'Classes', 'value' => 'Staff.StaffClasses'],
            ['text' => 'Subjects', 'value' => 'Staff.StaffSubjects'],
            ['text' => 'Leave', 'value' => 'Institution.StaffLeave'],
            ['text' => 'Attendances', 'value' => 'Institution.InstitutionStaffAttendances'],
            ['text' => 'Behaviours', 'value' => 'Staff.StaffBehaviours'],
            ['text' => 'Appraisals', 'value' => 'Institution.StaffAppraisals'],
            ['text' => 'Qualifications', 'value' => 'Staff.Qualifications'],
            ['text' => 'Extracurriculars', 'value' => 'Staff.Extracurriculars'],
            ['text' => 'Memberships', 'value' => 'Staff.Memberships'],
            ['text' => 'Licenses', 'value' => 'Staff.Licenses'],
            ['text' => 'Salaries', 'value' => 'Staff.Salaries'],
            ['text' => 'Needs', 'value' => 'Institution.StaffTrainingNeeds'],
            ['text' => 'Applications', 'value' => 'Institution.StaffTrainingApplications'],
            ['text' => 'Results', 'value' => 'Institution.StaffTrainingResults'],
            ['text' => 'Courses', 'value' => 'Staff.StaffTrainings'],
        ],
        'Health' => [
            ['text' => 'Overview', 'value' => 'Health.Healths'],
            ['text' => 'Allergies', 'value' => 'Health.Allergies'],
            ['text' => 'Consultations', 'value' => 'Health.Consultations'],
            ['text' => 'Families', 'value' => 'Health.Families'],
            ['text' => 'Histories', 'value' => 'Health.Histories'],
            ['text' => 'Immunizations', 'value' => 'Health.Immunizations'],
            ['text' => 'Medications', 'value' => 'Health.Medications'],
            ['text' => 'Tests', 'value' => 'Health.Tests'],
            ['text' => 'Body Mass', 'value' => 'User.UserBodyMasses'],
            ['text' => 'Insurances', 'value' => 'User.UserInsurances'],
            ['text' => 'PeopleDisabilities', 'value' => 'Health.PeopleDisabilities'],
        ],
        'Special Needs' => [
            ['text' => 'Referrals', 'value' => 'SpecialNeeds.SpecialNeedsReferrals'],
            ['text' => 'Assessments', 'value' => 'SpecialNeeds.SpecialNeedsAssessments'],
            ['text' => 'Services', 'value' => 'SpecialNeeds.SpecialNeedsServices'],
            ['text' => 'Devices', 'value' => 'SpecialNeeds.SpecialNeedsDevices'],
            ['text' => 'Plans', 'value' => 'SpecialNeeds.SpecialNeedsPlans'],
        ],
        'Attendance' => [
            ['text' => 'Students', 'value' => 'Institution.StudentAttendances'],
            ['text' => 'Staff', 'value' => 'Institution.StaffAttendances'],
        ],
        'Behaviour' => [
            ['text' => 'Students', 'value' => 'Institution.StudentBehaviours'],
            ['text' => 'Staff', 'value' => 'Institution.StaffBehaviours'],
        ],
        'Performance' => [
            ['text' => 'Competencies', 'value' => 'Institution.StudentCompetencies'],
            ['text' => 'Outcomes', 'value' => 'Institution.StudentOutcomes'],
            ['text' => 'Assessments', 'value' => 'Institution.InstitutionAssessments'],
        ],
        'Risks' => [
            ['text' => 'Risks', 'value' => 'Institution.InstitutionRisks'],
        ],
        'Examinations' => [
            ['text' => 'Exams', 'value' => 'Institution.InstitutionExaminations'],
            ['text' => 'Students', 'value' => 'Institution.InstitutionExaminationStudents'],
            ['text' => 'Results', 'value' => 'Institution.ExaminationResults'],
            ['text' => 'Gak', 'value' => 'Institution.InstitutionExaminationGak'],
        ],
        'Report Cards' => [
            ['text' => 'Comments', 'value' => 'Institution.ReportCardComments'],
            ['text' => 'Statuses', 'value' => 'Institution.ReportCardStatuses'],
        ],
        'Positions' => [
            ['text' => 'Positions', 'value' => 'Institution.InstitutionPositions'],
        ],
        'Finance' => [
            ['text' => 'Bank Accounts', 'value' => 'Institution.InstitutionBankAccounts'],
            ['text' => 'Institution Fees', 'value' => 'Institution.InstitutionFees'],
            ['text' => 'Student Fees', 'value' => 'Institution.StudentFees'],
            ['text' => 'Scholarships', 'value' => 'Institution.InstitutionScholarships'],
            ['text' => 'Expenditure', 'value' => 'Institution.InstitutionExpenditure'],
            ['text' => 'Entrance', 'value' => 'Institution.InstitutionEntrance'],
            ['text' => 'Estimate', 'value' => 'Institution.InstitutionEstimate'],
            ['text' => 'Institution Spc', 'value' => 'Institution.InstitutionSpc'],
        ],
        'Infrastructures' => [
            ['text' => 'Institution Lands', 'value' => 'Institution.InstitutionLands'],
            ['text' => 'Institution Buildings', 'value' => 'Institution.InstitutionBuildings'],
            ['text' => 'Institution Floors', 'value' => 'Institution.InstitutionFloors'],
            ['text' => 'Institution Rooms', 'value' => 'Institution.InstitutionRooms'],
            ['text' => 'MTB', 'value' => 'Institution.MonMtb'],
            ['text' => 'Needs', 'value' => 'Institution.InfrastructureNeeds'],
            ['text' => 'Projects', 'value' => 'Institution.InfrastructureProjects'],
            ['text' => 'Wash Waters', 'value' => 'Institution.InfrastructureWashWaters'],
            ['text' => 'Wash Sanitations', 'value' => 'Institution.InfrastructureWashSanitations'],
            ['text' => 'Wash Hygienes', 'value' => 'Institution.InfrastructureWashHygienes'],
            ['text' => 'Wash Wastes', 'value' => 'Institution.InfrastructureWashWastes'],
            ['text' => 'Wash Sewages', 'value' => 'Institution.InfrastructureWashSewages'],
            ['text' => 'Utility Electricities', 'value' => 'Institution.InfrastructureUtilityElectricities'],
            ['text' => 'Utility Internets', 'value' => 'Institution.InfrastructureUtilityInternets'],
            ['text' => 'Utility Telephones', 'value' => 'Institution.InfrastructureUtilityTelephones'],
            ['text' => 'Assets', 'value' => 'Institution.InstitutionAssets'],
            ['text' => 'FixedAssets', 'value' => 'Institution.FixedAssets'],
        ],
        'Survey' => [
            ['text' => 'Forms', 'value' => 'Institution.InstitutionSurveys'],
            ['text' => 'Rubrics', 'value' => 'Institution.InstitutionRubrics'],
        ],
        'Visit' => [
            ['text' => 'Requests', 'value' => 'Quality.VisitRequests'],
            ['text' => 'Visits', 'value' => 'Quality.InstitutionQualityVisits'],
        ],
        'Transport' => [
            ['text' => 'Providers', 'value' => 'Institution.InstitutionTransportProviders'],
            ['text' => 'Buses', 'value' => 'Institution.InstitutionBuses'],
            ['text' => 'Trips', 'value' => 'Institution.InstitutionTrips'],
        ],
        'Cases' => [
            ['text' => 'Cases', 'value' => 'Cases.InstitutionCases'],
        ],
        'Committees' => [
            ['text' => 'Committees', 'value' => 'Institution.InstitutionCommittees'],
        ]
    ];

    protected $plugins = [
        'Institution',
    ];

    protected $excludedFields = [
        'id',
    ];

    protected $orderParams = [
        'key' => null,
        'controlType' => 'section',
        'label' => null,
        'sortable' => 1,
        'visible' => true,
        'foreignKey' => null,
        'attributes' => []
    ];

    protected $connection;
    protected $controller;
    protected $model;
    protected $modelName;

    protected $monSections;
    protected $monFields;
    protected $monDropdowns;

    public $request;


    /**
     * Methods
     */

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->connection = ConnectionManager::get('default');
        $this->controller = $this->_registry->getController();
        $this->model = $this->controller->modelClass;
        $modelName = explode('.', $this->model);
        $this->modelName = isset($modelName[1]) ? $modelName[1] : null;
        $this->monSections = TableRegistry::get('MonFields.MonSections');
        $this->monFields = TableRegistry::get('MonFields.MonFields');
        $this->monDropdowns = TableRegistry::get('MonFields.MonDropdowns');
    }

    public function beforeFilter(Event $event)
    {
        $controller = $this->controller;
        $modelName = &$this->modelName;
        if ($modelName) {
            if (isset($controller->$modelName)) {
                $modelName .= 'Model';
            }
            $controller->$modelName = TableRegistry::get($this->model);
        }
        $controller->$modelName->controller = $this->controller;
    }

    public function startup(Event $event)
    {
        $controller = $this->controller;
        $controller->helpers['MonFields.MonFields'] = null;
        $action = $this->request->action;
        $plugin = $this->request->plugin;
        $data = $this->request->data;
        $viewBuilder = $controller->viewBuilder();

        if ($controller && $viewBuilder && $plugin === 'MonFields' && in_array($action, ['add', 'edit'])) {
            $viewBuilder->template('MonFields.Page/add');
        }

        if (isset($data[$controller->name])) {
            $fields = $this->monFields->getFields($this->model);
            if ($fields) {
                foreach ($fields as $field) {
                    if (isset($data[$controller->name][$field->name]['_ids'])) {
                        $this->request->data[$controller->name][$field->name] = serialize($data[$controller->name][$field->name]['_ids']);
                    }
                }
            }
        }
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Controller.Page.onRenderModel'] = 'onRenderModel';
        $events['View.beforeRender'] = 'beforeViewRender';
        return $events;
    }

    public function onRenderModel(Event $event, Entity $entity, PageElement $element)
    {
        $this->loadModels($entity);
    }

    public function beforeViewRender(Event $event)
    {
        $this->addSections($event);
        $this->addDropdowns($event);
        $this->addRelations($event);
    }

    /**
     * Loading models list on render the model field
     */
    public function loadModels(Entity $entity)
    {
        $action = $this->request->action;
        $page = $this->controller->Page;
        $currentColumns = array_diff($this->getTableColumns($this->model), $this->excludedFields);

        if ($action && $page && $currentColumns) {
            $field = null;

            if (in_array('before_field', $currentColumns)) {
                $field = 'before_field';
            } else if (in_array('after_field', $currentColumns)) {
                $field = 'after_field';
            }

            if ($field) {
                if (in_array($action, ['add', 'view', 'edit'])) {
                    $page->get($field)->setControlType('hidden');
                } else {
                    $page->get($field)->setDisabled(true);
                }

                if (in_array('model', $currentColumns)) {
                    $fieldOptions = $this->buildFieldOptions();
                    $this->setPageModels('model', true, $fieldOptions);

                    if ($entity->model) {
                        $columns = $this->getTableColumns($entity->model);
                        $action === 'edit' && $entity->name ? $this->excludedFields[] = $entity->name : null;
                        $formattedColumns = array_diff($this->formatTableColumns($columns), $this->excludedFields);

                        if (in_array($action, ['edit', 'delete'])) {
                            $page->get('model')->setDisabled(true);
                        }

                        if (in_array($action, ['index', 'view', 'add', 'edit'])) {
                            $page->get($field)->setControlType('select')->setOptions($formattedColumns)->setDisabled(false);
                        }
                    }
                }
            }
        }
    }

    public function buildFieldOptions(bool $sort = true)
    {
        $this->controller->loadComponent('FieldOption.FieldOption');
        $data = $this->controller->FieldOption->getFieldOptions();
        $fieldOptions = [];
        foreach ($data as $key => $obj) {
            if (isset($obj['title'])) {
                $keyName = $obj['title'];
            } else {
                $keyName = Inflector::humanize(Inflector::underscore($key));
            }
            $parent = __($obj['parent']);
            $fieldOptions[$parent][] = [
                'value' => $obj['parent'].'.'.$key,
                'text' => __($keyName)
            ];
        }

        if ($sort) {
            $fieldOptions = $this->sortModels($fieldOptions);
        }

        return $fieldOptions;
    }

    /**
     * Adding sections on render views
     */
    public function addSections(Event $event)
    {
        $action = $this->request->action;
        $sections = $this->monSections->getSections($this->model);

        if ($sections && $action !== 'index') {
            $elements = &$event->subject()->viewVars['elements'];
            foreach ($sections as $section) {
                $elements = $this->order($section['name'], $section['before_field'], $elements, false);
            }
        }
    }

    /**
     * Adding dropdown values list on render
     */
    public function addDropdowns(Event $event)
    {
        $action = $this->request->action;
        $fields = $this->monFields->getFields($this->model, 'dropdown');

        if ($action && $fields) {
            if ($action === 'index') {
                $data = &$event->subject()->viewVars['data'];
                if ($data) {
                    foreach ($fields as $field) {
                        $dropdowns = $this->monDropdowns->getDropdown($field->id);
                        $dropdowns = $dropdowns ? $this->translateDropdowns($dropdowns) : [];
                        foreach ($data as $item) {
                            if (isset($item->{$field->name})) {
                                $value = $item->{$field->name};
                                $values = @unserialize($value);
                                if ($values) {
                                    $valuesData = [];
                                    foreach($values as $value) {
                                        $valuesData[] = $dropdowns[$value];
                                    }
                                    $item[$field->name] = implode(', ', $valuesData);
                                } else if (isset($dropdowns[$value])) {
                                    $item[$field->name] = $dropdowns[$value];
                                }
                            }
                        }
                    }
                }
            } else {
                $elements = &$event->subject()->viewVars['elements'];
                foreach ($fields as $field) {
                    $dropdowns = $this->monDropdowns->getDropdown($field->id);
                    $dropdowns = $dropdowns ? $this->translateDropdowns($dropdowns) : [];
                    $elements[$field->name]['controlType'] = 'select';
                    $elements[$field->name]['options'] = $dropdowns;
                    $params = $field->params ? unserialize($field->params) : [];
                    if (isset($params['is_multiple']) && $params['is_multiple']) {
                        $elements[$field->name]['attributes']['multiple'] = true;
                        $elements[$field->name]['attributes']['name'] = $elements[$field->name]['attributes']['name'] . '._ids';
                    }
                    $value = isset($elements[$field->name]['attributes']['value']) ? $elements[$field->name]['attributes']['value'] : null;
                    if (!is_null($value)) {
                        $values = @unserialize($value);
                        if ($values) {
                            $valuesData = [];
                            foreach($values as $value) {
                                $valuesData[] = $dropdowns[$value];
                            }
                            $elements[$field->name]['attributes']['value'] = $action !== 'view' ?
                                $values :
                                implode(', ', $valuesData);
                        } else if (isset($dropdowns[$value])) {
                            $elements[$field->name]['attributes']['value'] = $dropdowns[$value];
                        }
                    }
                }
            }
        }
    }

    /**
     * Adding relation values list on render
     */
    public function addRelations(Event $event)
    {
        $action = $this->request->action;
        $fields = $this->monFields->getFields($this->model, 'relation');

        if ($action && $fields) {
            if ($action === 'index') {
                $data = &$event->subject()->viewVars['data'];
                if ($data) {
                    foreach ($fields as $field) {
                        $relatedModel = $this->monDropdowns->getDropdown($field->id, true)->model;
                        $relation = $this->getTableData($relatedModel);
                        $relation = $relation ? $this->translateDropdowns($relation) : [];
                        foreach ($data as $item) {
                            if (isset($item->{$field->name})) {
                                $value = $item->{$field->name};
                                $values = @unserialize($value);
                                if ($values) {
                                    $valuesData = [];
                                    foreach($values as $value) {
                                        $valuesData[] = $relation[$value];
                                    }
                                    $item[$field->name] = implode(', ', $valuesData);
                                } else if (isset($relation[$value])) {
                                    $item[$field->name] = $relation[$value];
                                }
                            }
                        }
                    }
                }
            } else {
                $elements = &$event->subject()->viewVars['elements'];
                foreach ($fields as $field) {
                    $relatedModel = $this->monDropdowns->getDropdown($field->id, true)->model;
                    $relation = $this->getTableData($relatedModel);
                    $relation = $relation ? $this->translateDropdowns($relation) : [];
                    $elements[$field->name]['controlType'] = 'select';
                    $elements[$field->name]['options'] = $relation;
                    $params = $field->params ? unserialize($field->params) : [];
                    if (isset($params['is_multiple']) && $params['is_multiple']) {
                        $elements[$field->name]['attributes']['multiple'] = true;
                        $elements[$field->name]['attributes']['name'] = $elements[$field->name]['attributes']['name'] . '._ids';
                    }
                    $value = isset($elements[$field->name]['attributes']['value']) ? $elements[$field->name]['attributes']['value'] : null;
                    if ($value) {
                        $values = @unserialize($value);
                        if ($values) {
                            $valuesData = [];
                            foreach($values as $value) {
                                $valuesData[] = $relation[$value];
                            }
                            $elements[$field->name]['attributes']['value'] = $action !== 'view' ?
                                $values :
                                implode(', ', $valuesData);
                        } else if (isset($relation[$value])) {
                            $elements[$field->name]['attributes']['value'] = $relation[$value];
                        }
                    }
                }
            }
        }
    }


    /**
     * Other functionality
     */

    public function setPageModels(string $fieldName, bool $reloadOnChange = false, array $excludedModels = [], bool $models = true, bool $sort = true)
    {
        $page = $this->controller->Page;
        if ($models && $this->models) {
            $tablesList = $this->translateModels($this->models);
        } else {
            $tablesList = $this->excludeModels($this->formatPluginsTablesList($this->getPluginsTablesList()), $excludedModels);
        }

        if ($sort) {
            $tablesList = $this->sortModels($tablesList);
        }

        $page->get($fieldName)->setControlType('select')->setOptions($tablesList)->setAttributes([
            'class' => 'selectpicker monfields-model',
            'data-size' => '15',
            'data-dropup-auto' => 'false',
            'data-live-search' => 'true',
            'onchange' => $reloadOnChange ? '$("#reload").click();' : ''
        ]);
    }

    public function excludeModels(array $tablesList, array $excludedModels)
    {
        foreach ($tablesList as $plugin => $models) {
            if (isset($excludedModels[$plugin])) {
                foreach ($excludedModels[$plugin] as $excluded) {
                    foreach ($models as $key => $values) {
                        if ($values['value'] === $excluded['value']) {
                            unset($tablesList[$plugin][$key]);
                        }
                    }
                }
            }
        }

        return $tablesList;
    }

    public function translateModels(array $tablesList)
    {
        foreach ($tablesList as $plugin => $models) {
            foreach ($models as $key => $values) {
                if (isset($values['text']) && $values['text']) {
                    $tablesList[$plugin][$key]['text'] = __($values['text']);
                } else {
                    unset($tablesList[$plugin][$key]);
                }
            }
        }

        return $tablesList;
    }

    public function translateDropdowns(array $dropdowns)
    {
        $result = [];
        foreach ($dropdowns as $dropdown) {
            $result[] = __($dropdown);
        }

        return $result;
    }

    public function sortModels(array $tablesList)
    {
        ksort($tablesList);

        foreach ($tablesList as $plugin => $models) {
            array_multisort($tablesList[$plugin], SORT_ASC);
        }

        return $tablesList;
    }

    public function getTableData(string $model, bool $toArray = true, bool $list = true, array $where = [])
    {
        if (!$model) {
            return;
        }

        $table = TableRegistry::get($model);
        $result = [];

        if ($table) {
            $list = $list ? 'list' : '';
            $result = $table->find($list)->where($where)->all();
        }

        if ($result && $toArray) {
            $result = $result->toArray();
        }

        return $result;
    }

    public function getTableColumns(string $model)
    {
        $table = TableRegistry::get($model);

        if (!$table) {
            return false;
        }

        $this->cacheClear();

        return $table->schema()->columns();
    }

    public function formatTableColumns(array $columns)
    {
        $fields = [];
        $fieldsExcluded = array_diff($columns, $this->excludedFields);

        foreach ($fieldsExcluded as $column) {
            $columnName = str_replace('Id', '', Inflector::humanize(Inflector::underscore($column)));
            $columnName = __($columnName);
            $fields[$column] = $columnName;
        }

        return $fields;
    }

    public function getPluginsTablesList()
    {
        $pluginsTables = [];
        foreach ($this->plugins as $plugin) {
            $pluginsPath = self::P_DIR . $plugin . '/src/Model/Table';
            $folder = new Folder($pluginsPath);
            $pluginsTables[$plugin] = array_map(function($name) {
                return basename($name, self::EXT);
            }, $folder->find(self::T_EXT));
        }

        return $pluginsTables;
    }

    public function formatPluginsTablesList(array $pluginsTables)
    {
        $formatted = [];
        foreach ($pluginsTables as $plugin => $tables) {
            $pluginName = __($plugin);
            $formatted[$pluginName] = [];
            foreach ($tables as $table) {
                $formatted[$pluginName][] = [
                    'value' => "$plugin.$table",
                    'text' => __(Inflector::humanize(Inflector::underscore($table)))
                ];
            }
        }

        return $formatted;
    }

    public function cacheClear(bool $all = false)
    {
        $shellDispatcher = new ShellDispatcher();
        $cmd = ['cake', 'cache'];
        $all ? array_push($cmd, 'clear_all') : array_push($cmd, 'clear', '_cake_model_');

        return $shellDispatcher->run($cmd);
    }

    public function columnQuery(string $action, string $tableName, string $columnName, ?string $type, ?bool $null, ?string $afterColumn, int $length = null, int $afterLength = null)
    {
        $connection = $this->connection;
        $alterTable = "ALTER TABLE `$tableName`";
        $afterColumn = $afterColumn ? "AFTER `$afterColumn`" : "";
        $length && $afterLength ? $length .= ", $afterLength" : "";
        $length ? $type .= "($length)" : "";
        $null = $null ? "NULL" : "NOT NULL";
        $query = null;

        if ($action === 'add') {
            $query = "$alterTable ADD `$columnName` $type $null $afterColumn";
        } else if ($action === 'edit') {
            $query = "$alterTable MODIFY COLUMN `$columnName` $type $null $afterColumn";
        } else if ($action === 'delete') {
            $query = "$alterTable DROP COLUMN `$columnName`";
        }

        if ($query) {
            return $connection->execute($query);
        }

        return false;
    }

    public function order(string $name, string $field, array $elements, bool $after = true)
    {
        $result = [];
        foreach ($elements as $key => $element) {
            $after ? $result[$key] = $element : null;
            if (!in_array($name, $elements) && $key === $field) {
                $result[$name] = array_merge($this->orderParams, ['label' => __($name)]);
            }
            !$after ? $result[$key] = $element : null;
        }

        return $result;
    }
}
