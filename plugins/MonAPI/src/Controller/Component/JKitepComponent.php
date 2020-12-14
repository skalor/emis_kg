<?php
namespace MonAPI\Controller\Component;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Controller\Component;

class JKitepComponent extends Component
{
    private $controller;
    private $session;
    private $model;
    private $restful;
    private $url = "https://kabinet.kitep.org.kg/webservice.php";
    private $key = "lJHVSoHvrP4OILLZ";
    private $params = [
        'operation' => 'getchallenge',
        'username' => 'admin',
    ];
    private $tables = [
        'Institution.Institutions' => [
            'Schools' => []
        ],
        'Institution.InstitutionClasses' => [
            'SchoolClasses' => [
                [
                    'column' => 'is_active',
                    'operation' => '!=',
                    'value' => "' '"
                ]
            ]
        ],
        'Institution.Staff' => [
            'SchoolStaff' => []
        ],
        'Institution.Students' => [
            'SchoolClasses' => []
        ]
    ];
    
    public function initialize(array $config)
    {
        $this->controller = $this->_registry->getController();
        $this->session = $this->request->session();
    }
    
    public function beforeFilter(Event $event)
    {
        $this->model = $this->request->model;
        $this->restful = $this->controller->Restful;
        $this->session->write('Areas', $this->controller->Areas->find()->all()->toArray());
    }
    
    public function auth()
    {
        $getToken = json_decode($this->restful->curl($this->url, $this->params), true);
        if (isset($getToken['result']['token'])) {
            $token = $getToken['result']['token'];
            $this->params['operation'] = 'login';
            $this->params['accessKey'] = md5($token . $this->key);
            $sessionName = json_decode($this->restful->curl($this->url, $this->params, 'POST'), true);

            if (isset($sessionName['result']['sessionName'])) {
                $this->session->write('JKitepSessionName', $sessionName['result']['sessionName']);
                $this->unsetParams();
                
                return true;
            }
            
            return $token;
        }
        
        return false;
    }
    
    public function unsetParams()
    {
        $this->params['operation'] = 'listtypes';
        $this->params['sessionName'] = $this->session->read('JKitepSessionName');
        unset($this->params['username']);
        unset($this->params['accessKey']);
    }
    
    public function buildQueries(bool $semicolon = true, bool $counting = false, bool $all = false)
    {
        $tables = $this->tables;
        $tableConds = [];
        if (!$this->restful->getIfExists($tables))
            return false;
        if ($this->restful->getIfExists($tables) && is_array($tables))
            $tableConds = !$all && $this->model && isset($tables[$this->model])
                ? [$this->model => $tables[$this->model]] : $tables;
            
        $queries = [];
        if ($tableConds) {
            foreach ($tableConds as $model => $item) {
                if ($this->restful->getIfExists($item) && is_array($item)) {
                    foreach ($item as $table => $conds) {
                        $count = $counting ? 'COUNT(*)' : '*';
                        $queries[$model] = "SELECT " . $count . ' FROM ' . $table;
                        if ($this->restful->getIfExists($conds) && is_array($conds)) {
                            $condsLength = count($conds);
                            foreach ($conds as $key => $cond) {
                                if (
                                    $this->restful->getIfExists($cond['column']) &&
                                    $this->restful->getIfExists($cond['operation']) &&
                                    $this->restful->getIfExists($cond['value'])
                                ) {
                                    $where = $key == 0 ? ' WHERE ' : '';
                                    $sc = $semicolon && ($condsLength - 1) == $key ? ';' : '';
                                    $and = ($condsLength - 1) != $key ? ' AND ' : $sc;
                                    $condStr = $where . implode(' ', $cond) . $and;
                                    $queries[$model] .= $condStr;
                                }
                            }
                        } else if($semicolon) {
                            $queries[$model] .= ";";
                        }
                    }
                }
            }
        }
        
        return $queries;
    }
    
    public function getQueriesCount()
    {
        $queries = $this->buildQueries(true, true, true);
        if (!$this->session->check('queriesCount') && $this->restful->getIfExists($queries)) {
            $parsedQueries = [];
            foreach ($queries as $key => $query) {
                $this->params['query'] = $query;
                $count = json_decode($this->restful->curl($this->url, $this->params), true);
                if ($this->restful->getIfExists($count['result'][0]['count']))
                    $parsedQueries[$key] = $count['result'][0]['count'];
            }
            $this->session->write('queriesCount', $parsedQueries);
        }
        
        return $this->session->read('queriesCount');
    }
    
    public function getQueries(int $page = 1, int $limit = 10)
    {
        if ($page && $page < 1)
            $page = 1;
        if ($limit && $limit < 1)
            $limit = 10;
        
        $queriesTotal = $this->getQueriesCount();
        $queriesBuilded = $this->buildQueries(false);
        $queries = [];
        
        if ($limit && $this->restful->getIfExists($queriesTotal) && $this->restful->getIfExists($queriesBuilded)) {
            foreach ($queriesTotal as $model => $total) {
                if ($page) {
                    $offset = ($page - 1) * $limit;
                    foreach ($queriesBuilded as $key => $value) {
                        if ($model == $key)
                            $queries[$key][] = $value . " LIMIT $offset, $limit;";
                    }
                } else {
                    $offset = ceil($total / $limit);
                    for ($i = 0; $i < $offset; $i += $limit) {
                        foreach ($queriesBuilded as $key => $value) {
                            if ($model == $key)
                                $queries[$key][] = $value . " LIMIT $i, $limit;";
                        }
                    }
                }
            }
        }
        
        return $queries;
    }
    
    public function parse(int $sleep = 1)
    {
        if (!$this->session->check('JKitepSessionName')) {
            $this->auth();
        } else {
            $this->unsetParams();
        }
        
        $this->params['operation'] = 'query';
        $data = [];
        $parseResults = [];
        $queries = $this->getQueries();
        
        foreach ($queries as $key => $query) {
            if ($this->restful->getIfExists($query)) {
                foreach ($query as $value) {
                    $this->params['query'] = $value;
                    $result = json_decode($this->restful->curl($this->url, $this->params), true);
                    
                    if (isset($result['result'])) {
                        $method = lcfirst(explode('.', $key)[1]) . 'Operation';
                        $data[$key] = $this->{$method}($result['result']);

                        if (!is_array($data[$key]))
                            continue;

                        $this->request->data = $data[$key];
                        $model = $this->restful->instantiateModel($key);
                        $this->restful->model = $model;
                        $parseResults[$key] = $this->restful->add(false);
                    }

                    sleep($sleep);
                }
            }
        }
        
        $this->restful->serialize->offsetSet('Result', $parseResults);
        
        return $parseResults;
    }
    
    
    /**
     * Operations list
     */
    
    /**
     * Institution operation function
     * @param type array
     * @return array
     */
    public function institutionsOperation($arr)
    {
        if (!empty($arr)) {
            $regions = [];
            foreach ($arr as $item)
                $regions[] = $item['region_id'];
            $regions = $this->getRegions($regions);
            
            if ($this->restful->getIfExists($regions)) {
                $data = [];
                foreach ($regions as $region) {
                    foreach ($this->session->read('Areas') as $area) {
                        foreach ($arr as $key => $item) {
                            if ($region['id'] == $item['region_id'])
                                $coate = $region['coate'];
                            else $coate = null;

                            if ($coate && $area->get('code') == $coate)
                                $regionId = $area->get('id');
                            else continue;

                            $institution = $this->restful->instantiateModel('Institution.Institutions');
                            $school = $institution->find()->where(['code' => $item['okpo']])->first();
                            
                            if ($school)
                                continue;
                            
                            $data[] = [
                                'superAdmin' => 1,
                                'userId' => null,
                                'name' => $this->restful->getIfExists($item['school_named']),
                                'alternative_name' => $this->restful->getIfExists($item['school_number']),
                                'code' => $this->restful->getIfExists($item['okpo']),
                                //'postal_code' => $this->restful->getIfExists($item['postal_code']),
                                //'contact_person' => $this->restful->getIfExists($item['contact_person']),
                                'telephone' => $this->restful->getIfExists($item['work_phone']),
                                'email' => $this->restful->getIfExists($item['email']),
                                'website' => $this->restful->getIfExists($item['email']),
                                'address' => $this->restful->getIfExists($item['school_address']),
                                'date_opened' => $this->restful->getIfExists($item['found_school'], date('Y-m-d')),
                                'classification' => 1,
                                'longitude' => 72.4,
                                'latitude' => 42.3,
                                'area_id' => $regionId,
                                'area_administrative_id' => null,
                                'institution_locality_id' => 1,
                                'institution_type_id' => 1,
                                'institution_ownership_id' => 1,
                                'institution_sector_id' => 1,
                                'institution_provider_id' => 2,
                                'institution_gender_id' => 1
                            ];
                        }
                    }
                }
                
                return $data;
            }
        }
        
        return false;
    }
    
    public function getRegions($regionIdsArr, $notempty = true)
    {
        $regions = implode(', ', array_unique($regionIdsArr));
        $this->params['query'] = "SELECT * FROM Region WHERE id IN ($regions);";
        $regions = json_decode($this->restful->curl($this->url, $this->params), true);
        
        if ($this->restful->getIfExists($regions['result']) && is_array($regions['result'])) {
            $regions = $regions['result'];
            if ($notempty) {
                foreach ($regions as $key => $item) {
                    if (!$this->restful->getIfExists($item['coate']))
                        unset($regions[$key]);
                }
            }
            
            return $regions;
        }
        
        return false;
    }
    
    /**
     * Institution classes operation function
     * @param type array
     * @return array
     */
    public function institutionClassesOperation($arr)
    {
        if (!empty($arr)) {
            $schools = [];
            $classes = [];
            foreach ($arr as $item) {
                $schools[] = $item['school_id'];
                $classes[] = $item['class_id'];
            }
            $schools = $this->getSchools($schools);
            $classes = $this->getClasses($classes);
            
            if ($this->restful->getIfExists($schools) && $this->restful->getIfExists($classes)) {
                $data = [];
                foreach ($schools as $school) {
                    foreach ($classes as $class) {
                        foreach ($arr as $key => $item) {
                            if ($item['school_id'] == $school['id'] && $class['id'] == $item['class_id']) {
                                $institution = $this->restful->instantiateModel('Institution.Institutions');
                                $schoolExists = $institution->find()->where(['code' => $school['okpo']])->first();
                                
                                if (!$schoolExists)
                                    continue;
                                
                                $shift = $this->getShift($schoolExists->get('id'));
                                
                                if (!$shift)
                                    continue;
                                
                                $shiftId = $shift->get('id');
                                $academicPID = $shift->get('academic_period_id');
                                $educationGrade = $this->getProgramme($schoolExists->get('id'), $class);
                                
                                if (!$educationGrade)
                                    continue;
                                
                                $data[] = [
                                    'superAdmin' => 1,
                                    'userId' => null,
                                    'name' => $this->restful->getIfExists($class['class_name']),
                                    'capacity' => 50,
                                    'education_grades' => [$educationGrade->toArray()],
                                    'staff_id' => 0,
                                    'institution_shift_id' => $shiftId,
                                    'institution_id' => $schoolExists->get('id'),
                                    'academic_period_id' => $academicPID
                                ];
                            }
                        }
                    }
                }
                
                return $data;
            }
        }
        
        return false;
    }
    
    public function getShift($institutionId)
    {
        if (!$institutionId || !is_int($institutionId))
            return false;
            
        $academicPeriods = $this->controller->AcademicPeriods;
        $academicPeriod = $academicPeriods->find()->where(['start_year' => date('Y')])->first();
        
        if ($academicPeriod) {
            $shifts = $this->controller->InstitutionShifts;
            $shift = $shifts->find()->where([
                'institution_id' => $institutionId,
                'academic_period_id' => $academicPeriod->get('id')
            ])->first();

            if (!$shift) {
                $options = ['extra' => $this->restful->extra];
                $shiftData = [
                    'location_institution_id' => $institutionId,
                    'academic_period_id' => $academicPeriod->get('id'),
                    'institution_id' => $institutionId,
                    'shift_option_id' => 1,
                    'start_time' => '07:00 AM',
                    'end_time' => '07:00 PM'
                ];
                $shift = $shifts->newEntity($shiftData, $options);
                $shifts->save($shift, $options);
            }

            return $shift;
        }
        
        return false;
    }
    
    public function getProgramme($institutionId, $class)
    {
        if (!$institutionId || !is_int($institutionId) || !$class)
            return false;
        
        $grades = $this->controller->EducationGrades;
        $programs = $this->controller->InstitutionGrades;
        $program = $programs->find()->where(['institution_id' => $institutionId])->all()->toArray();
        $className = $class['class_num'] . " класс";
        $notMatch = false;
        
        if ($program) {
            foreach ($program as $value) {
                $grade = $grades->get($value->get('education_grade_id'));
                if ($grade && $grade->get('code') == $className)
                    return $grade;
                else
                    $notMatch = true;
            }
        }
        
        if (!$program || $notMatch) {
            $grade = $grades->find()->where(['code' => $className])->first();
            if ($grade) {
                $options = ['extra' => $this->restful->extra];
                $programsData = [
                    'start_date' => date('d-m-Y'),
                    'programme' => $grade->get('education_programme_id'),
                    'education_grade_id' => $grade->get('id'),
                    'institution_id' => $institutionId
                ];
                $program = $programs->newEntity($programsData, $options);
                $programs->save($program, $options);
                
                return $grade;
            }
        }
        
        return false;
    }
    
    public function getSchools($schoolIdsArr, $notempty = true)
    {
        $schools = implode(', ', array_unique($schoolIdsArr));
        $this->params['query'] = "SELECT * FROM Schools WHERE id IN ($schools);";
        $schools = json_decode($this->restful->curl($this->url, $this->params), true);
        
        if ($this->restful->getIfExists($schools['result']) && is_array($schools['result'])) {
            $schools = $schools['result'];
            if ($notempty) {
                foreach ($schools as $key => $item) {
                    if (!$this->restful->getIfExists($item['okpo']))
                        unset($schools[$key]);
                }
            }
            
            return $schools;
        }
        
        return false;
    }
    
    public function getClasses($classIdsArr, $notempty = true)
    {
        $classes = implode(', ', array_unique($classIdsArr));
        $this->params['query'] = "SELECT * FROM Classes WHERE id IN ($classes);";
        $classes = json_decode($this->restful->curl($this->url, $this->params), true);
        
        if ($this->restful->getIfExists($classes['result']) && is_array($classes['result'])) {
            $classes = $classes['result'];
            if ($notempty) {
                foreach ($classes as $key => $item) {
                    if (!$this->restful->getIfExists($item['class_name']) || !$this->restful->getIfExists($item['class_num']))
                        unset($classes[$key]);
                }
            }
            
            return $classes;
        }
        
        return false;
    }
    
    /**
     * Institution staff operation function
     * @param type array
     * @return array
     */
    public function staffOperation($arr)
    {
        if (!empty($arr)) {
            $data = [];
            /*$this->params['operation'] = 'listtypes';
            $classes = json_decode($this->restful->curl($this->url, $this->params), true);//*/
            dd($arr);
            return $arr;
        }
        
        return false;
    }
    
    /**
     * Institution students operation function
     * @param type array
     * @return array
     */
    public function studentsOperation($arr)
    {
        
        return false;
    }
}
