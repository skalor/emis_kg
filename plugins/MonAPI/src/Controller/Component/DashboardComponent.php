<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class DashboardComponent extends Component
{
    public $components = ['MonAPI.Table'];
    private $controller;
    private $restful;
    private $data;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->controller = $this->_registry->getController();
    }

    public function beforeFilter(Event $event)
    {
        $this->restful = $this->controller->Restful;
    }

    public function clear()
    {
        $this->data = null;
    }

    public function get()
    {
        $cache = $this->request->query('cache');
        $cachedData = $this->Table->getCachedTableData('', true);
        if ((is_null($cache) || $cache == true) && $cachedData) {
            $result = $cachedData;
        } else {
            $cmd = ROOT . DS . 'bin' . DS . 'cake DashboardData';
            $logs = ROOT . DS . 'logs' . DS . 'DashboardData.log & echo $!';
            $shellCmd = $cmd . ' >> ' . $logs;
            $pid = exec($shellCmd);
            //$this->generateJson();
            $result = 'Json file generating. Please try a few minutes later!';
        }

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function generateJson()
    {
        if (!file_exists(WWW_ROOT . 'DashboardData/')) {
            mkdir(WWW_ROOT . 'DashboardData/', 0775);
        }

        $waitFileName = WWW_ROOT . 'DashboardData/wait.txt';
        if (file_exists($waitFileName) && filemtime($waitFileName) > strtotime('-1 hour')) {
            return 'wait';
        }

        file_put_contents($waitFileName, 'wait');
        set_time_limit(600);
        ignore_user_abort(true);
        $allData = $this->allData(false, false);
        $cachedTableData = $this->Table->cacheTableData($allData);
        unlink($waitFileName);

        return $cachedTableData;
    }

    private function allData(?bool $isAdministrative = false, ?bool $onlyInstitutions = true)
    {
        $data = $this->data;
        if ($data && isset($data['allData'])) {
            return $data['allData'];
        }

        $model = $isAdministrative ? 'AreaAdministrativeLevels' : 'AreaLevels';
        $areaLevelId = $isAdministrative ? 'area_administrative_level_id' : 'area_level_id';

        $contain = [
            'Areas' => [
                'fields' => [
                    'Areas.id',
                    'Areas.code',
                    'Areas.name',
                    'Areas.' . $areaLevelId,
                    'Areas.parent_id'
                ],
                'Institutions' => [
                    'fields' => [
                        'Institutions.id',
                        'Institutions.pin',
                        'Institutions.code',
                        'Institutions.name',
                        'Institutions.shift_type',
                        'Institutions.postal_code',
                        'Institutions.contact_person',
                        'Institutions.email',
                        'Institutions.telephone',
                        'Institutions.website',
                        'Institutions.address',
                        'Institutions.date_opened',
                        'Institutions.date_closed',
                        'Institutions.longitude',
                        'Institutions.latitude',
                        'Institutions.area_id',
                        'Institutions.area_administrative_id'
                    ],
                    'Types' => [
                        'fields' => [
                            'Types.name',
                            'Types.international_code',
                            'Types.national_code'
                        ]
                    ],
                    'Localities' => [
                        'fields' => [
                            'Localities.name',
                            'Localities.international_code',
                            'Localities.national_code'
                        ]
                    ],
                    'Genders' => [
                        'fields' => [
                            'Genders.code',
                            'Genders.name'
                        ]
                    ],
                    'Statuses' => [
                        'fields' => [
                            'Statuses.code',
                            'Statuses.name'
                        ]
                    ],
                    'Sectors' => [
                        'fields' => [
                            'Sectors.name',
                            'Sectors.international_code',
                            'Sectors.national_code'
                        ]
                    ],
                    'Providers' => [
                        'fields' => [
                            'Providers.name',
                            'Providers.international_code',
                            'Providers.national_code'
                        ]
                    ],
                    'Ownerships' => [
                        'fields' => [
                            'Ownerships.name',
                            'Ownerships.international_code',
                            'Ownerships.national_code'
                        ]
                    ]
                ]
            ]
        ];

        if (!$onlyInstitutions) {
            $classes = $this->getClasses();
            $positions = $this->getPositions();
            $contain['Areas']['Institutions']['InstitutionClasses'] = $classes['InstitutionClasses'];
            $contain['Areas']['Institutions']['InstitutionPositions'] = $positions['InstitutionPositions'];
        }

        $result = TableRegistry::get('Area.' . $model)->find()->contain($contain)->all()->toArray();

        $this->data['allData'] = $result;

        return $result;
    }

    public function getClasses()
    {
        $queries = isset($this->request->query) ? $this->request->query : [];
        $academicPeriodCond = isset($queries['academic_period_id']) ? ['InstitutionClasses.academic_period_id' => $queries['academic_period_id']] : [];

        $data = [
            'InstitutionClasses' => [
                'fields' => [
                    'InstitutionClasses.institution_id',
                    'InstitutionClasses.name',
                    'InstitutionClasses.capacity',
                    'InstitutionClasses.total_male_students',
                    'InstitutionClasses.total_female_students',
                ],
                'conditions' => $academicPeriodCond,
                'AcademicPeriods' => [
                    'fields' => [
                        'AcademicPeriods.code',
                        'AcademicPeriods.name',
                        'AcademicPeriods.start_date',
                        'AcademicPeriods.end_date',
                        'AcademicPeriods.current',
                        'AcademicPeriods.parent_id'
                    ]
                ],
                'InstitutionShifts' => [
                    'fields' => [
                        'InstitutionShifts.start_time',
                        'InstitutionShifts.end_time',
                    ],
                    'ShiftOptions' => [
                        'fields' => [
                            'ShiftOptions.name',
                            'ShiftOptions.international_code',
                            'ShiftOptions.national_code'
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    public function getPositions()
    {
        $data = [
            'InstitutionPositions' => [
                'fields' => [
                    'InstitutionPositions.id',
                    'InstitutionPositions.institution_id',
                    'InstitutionPositions.position_no',
                    'InstitutionPositions.is_homeroom'
                ],
                'Statuses' => [
                    'fields' => [
                        'Statuses.name',
                        'Statuses.category'
                    ]
                ],
                'StaffPositionTitles' => [
                    'fields' => [
                        'StaffPositionTitles.name',
                        'StaffPositionTitles.type',
                        'StaffPositionTitles.international_code',
                        'StaffPositionTitles.national_code'
                    ]
                ],
                'StaffPositionGrades' => [
                    'fields' => [
                        'StaffPositionGrades.name',
                        'StaffPositionGrades.international_code',
                        'StaffPositionGrades.national_code'
                    ]
                ],
                'InstitutionStaff' => [
                    'fields' => [
                        'InstitutionStaff.institution_position_id',
                        'InstitutionStaff.start_date',
                        'InstitutionStaff.end_date'
                    ],
                    'StaffTypes' => [
                        'fields' => [
                            'StaffTypes.name',
                            'StaffTypes.international_code',
                            'StaffTypes.national_code'
                        ]
                    ],
                    'StaffStatuses' => [
                        'fields' => [
                            'StaffStatuses.name',
                            'StaffStatuses.code'
                        ]
                    ],
                    'Users' => [
                        'fields' => [
                            'Users.first_name',
                            'Users.middle_name',
                            'Users.last_name',
                            'Users.date_of_birth',
                        ],
                        'Genders' => [
                            'fields' => [
                                'Genders.code',
                                'Genders.name'
                            ]
                        ],
                        'MainNationalities' => [
                            'fields' => [
                                'MainNationalities.name',
                                'MainNationalities.international_code',
                                'MainNationalities.national_code'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    public function getUsersCounter()
    {
        $result['students_total'] = $this->getStudentsCounter()->count();
        $result['staff_total'] = $this->getStaffCounter()->count();

        return $result;
    }

    public function getStudentsCounter()
    {
        $studentsTable = TableRegistry::get('Institution.Students');

//        $studentsTableColumns = $this->Table->clearAll()->setTable($studentsTable)->getTableColumns();
//        dump($studentsTableColumns, $this->request);

        /*$this->Table->setTableConditions([
            'AcademicPeriods.code' => 'YR2019',
            'OR' => [
                ['StudentStatuses.code' => 'CURRENT'],
                ['StudentStatuses.code' => 'TRANSFERRED']
            ]
        ]);*/

        return $this->setConditions($studentsTable);
    }

    public function getStaffCounter()
    {
        $staffTable = TableRegistry::get('Institution.Staff');

//        $staffTableColumns = $this->Table->clearAll()->setTable($staffTable)->getTableColumns();
//        dump($staffTableColumns, $this->request);

        return $this->setConditions($staffTable);
    }

    public function setConditions(Table $table, ?array $conditions = [])
    {
        $tableComponent = $this->Table;
        $tableComponent->clearAll()->setTable($table);
        if ($conditions) {
            $tableComponent->setTableConditions($conditions);
        } else {
            $tableComponent->setRequestQueries();
        }

        return $tableComponent->getTableData();
    }
}
