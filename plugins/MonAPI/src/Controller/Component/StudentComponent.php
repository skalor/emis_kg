<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;

class StudentComponent extends Component
{
    private $controller;
    private $session;
    private $restful;
    private $model;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->controller = $this->_registry->getController();
        $this->session = $this->request->session();
    }

    public function beforeFilter(Event $event)
    {
        $this->restful = $this->controller->Restful;
        $this->model = $this->restful->instantiateModel('Institution.Students');
    }

    public function startup(Event $event)
    {
        $this->controller->set('model', 'User.Users');
        $this->controller->set('error', null);
    }

    public function add(bool $return = false)
    {
        $data = $this->request->data;
        $result = ['data' => $data, 'error' => __('Please check your fields: code_okpo, first_name, last_name, date_of_birth')];
        $hasAccess = $this->controller->Institution->hasAccess($data['code_okpo']);

        if (!$hasAccess) {
            $result['error'] = __('You do not have access to this institution');
        }

        if (
            isset($data['code_okpo']) && !empty($data['code_okpo']) &&
            isset($data['first_name']) && !empty($data['first_name']) &&
            isset($data['last_name']) && !empty($data['last_name']) &&
            isset($data['date_of_birth']) && !empty($data['date_of_birth']) &&
            $hasAccess
        ) {
            $institution = $this->controller->Institution->get(['code' => $data['code_okpo']]);
            if (isset($institution[0])) {
                $institution = $institution[0];
                $class_id = $this->controller->Class->add($institution->get('id'), $data['education_grade_id']);
                if (!$class_id || !is_int($class_id)) {
                    $result = $class_id;
                } else {
                    $this->request->data['is_student'] = 1;
                    $result = $this->controller->User->add([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'date_of_birth' => $data['date_of_birth']
                    ]);
                }

                if (isset($result['error']) && empty($result['error'])) {
                    if (isset($data['mobile'])) {
                        $result['StudentContact'] = $this->controller->User->addContact($result['data']->get('id'));
                    }
                    if (isset($data['start_date']) && $class_id) {
                        $academicPeriods = $this->restful->instantiateModel('AcademicPeriod.AcademicPeriods');
                        $academicPeriod = $academicPeriods->find()->where(['start_date' => $data['start_date']])->first();
                        if (!$academicPeriod && isset($data['academic_period_id'])) {
                            $academicPeriod = $academicPeriods->find()->where(['id' => $data['academic_period_id']])->first();
                        }
                        if ($academicPeriod) {
                            $data['end_date'] = $this->request->data['end_date'] = $academicPeriod->get('end_date');
                        }

                        $result['InstitutionStudent'] = $this->addStudentToInstiution($result['data']->get('id'), $data['start_date'], $institution->get('id'), $class_id);
                    }
                }
            } else {
                $result = __('Institution not found');
            }
        }

        $this->controller->Restful->logging('institution_student_' . date('Y-m-d'), $result);

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function addMulti()
    {
        $data = $this->request->data;
        $count = count($data);
        $errorMsg = $count <= 1000 ? null : __("Max JSON elements must be <= 1000");
        $this->controller->set('error', $errorMsg);
        $result = ['data' => $data, 'error' => __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]')];

        if ($data && !$errorMsg) {
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $this->request->data = $item;
                    $result[] = $this->add(true);
                } else {
                    $result[] = __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]');
                }
            }
        }

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function update(bool $return = false, bool $update = true)
    {
        $data = $this->request->data;
        $result = ['data' => $data, 'error' => __('Please check your fields: code_okpo, id')];
        $hasAccess = $this->controller->Institution->hasAccess($data['code_okpo']);

        if (!$hasAccess) {
            $result['error'] = __('You do not have access to this institution');
        }

        if (
            isset($data['code_okpo']) && !empty($data['code_okpo']) &&
            isset($data['id']) && !empty($data['id']) &&
            $hasAccess
        ) {
            $institution = $this->controller->Institution->get(['code' => $data['code_okpo']]);
            if (isset($institution[0])) {
                $institution = $institution[0];
                $class_id = $this->controller->Class->add($institution->get('id'), $data['education_grade_id']);
                if (!$class_id || !is_int($class_id)) {
                    $result = $class_id;
                } else {
                    $result = $this->controller->User->update([
                        'id' => $data['id']
                    ], $update);
                }

                if (isset($result['error']) && empty($result['error'])) {
                    if (isset($data['mobile'])) {
                        $result['StudentContact'] = $this->controller->User->addContact($result['data']->get('id'), $update);
                    }
                    if (isset($data['start_date']) && $class_id) {
                        $result['InstitutionStudent'] = $this->addStudentToInstiution($result['data']->get('id'), $data['start_date'], $institution->get('id'), $class_id);
                    }
                }
            } else {
                $result = __('Institution not found');
            }
        }

        $this->controller->Restful->logging('institution_student_' . date('Y-m-d'), $result);

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function updateMulti()
    {
        $data = $this->request->data;
        $count = count($data);
        $errorMsg = $count <= 1000 ? null : __("Max JSON elements must be <= 1000");
        $this->controller->set('error', $errorMsg);
        $result = ['data' => $data, 'error' => __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]')];

        if ($data && !$errorMsg) {
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $this->request->data = $item;
                    $result[] = $this->update(true);
                } else {
                    $result[] = __('Please check your data. Your data must be like this: [ {...}, {...}, ... ]');
                }
            }
        }

        $this->restful->serialize->offsetSet('Result', $result);
    }

    public function get(array $where = [], bool $return = true)
    {
        $all = isset($where['_all']) && $where['_all'] ? true : false;
        $page = isset($where['_page']) && (int)$where['_page'] > 0 ? (int)$where['_page'] : 1;

        $conditions = [];
        $accessParams = $this->controller->Restful->accessParams;
        $accessParams['institutionsTypesIds'] && !$return ? $conditions['Institutions.institution_type_id IN'] = $accessParams['institutionsTypesIds'] : [];
        $accessParams['institutionsCodes'] && !$return && $accessParams['institutionsCodes'] !== '*' ? $conditions['Institutions.code IN'] = $accessParams['institutionsCodes'] : [];

        isset($where['created_from']) && $where['created_from'] ? $conditions['Students.created >='] = $where['created_from'] : null;
        isset($where['created_to']) && $where['created_to'] ? $conditions['Students.created <='] = $where['created_to'] : null;
        isset($where['student_id']) && $where['student_id'] ? $conditions['Students.student_id'] = $where['student_id'] : null;
        isset($where['institution_id']) && $where['institution_id'] ? $conditions['Students.institution_id'] = $where['institution_id'] : null;
        isset($where['academic_period_id']) && $where['academic_period_id'] ? $conditions['Students.academic_period_id'] = $where['academic_period_id'] : null;
        isset($where['education_grade_id']) && $where['education_grade_id'] ? $conditions['Students.education_grade_id'] = $where['education_grade_id'] : null;

        if (!$return) {
            $academicPeriodId = TableRegistry::get('AcademicPeriod.AcademicPeriods')->getCurrent();
            !isset($where['academic_period_id']) || !$where['academic_period_id'] ? $conditions['Students.academic_period_id'] = $academicPeriodId : null;
            isset($where['academic_period_id']) && $where['academic_period_id'] ? $conditions['Class.academic_period_id'] = $where['academic_period_id'] : $conditions['Class.academic_period_id'] = $academicPeriodId;
            isset($where['first_name']) && $where['first_name'] ? $conditions['Users.first_name'] = $where['first_name'] : null;
            isset($where['last_name']) && $where['last_name'] ? $conditions['Users.last_name'] = $where['last_name'] : null;
            isset($where['date_of_birth']) && $where['date_of_birth'] ? $conditions['Users.date_of_birth'] = $where['date_of_birth'] : null;
            isset($where['pin']) && $where['pin'] ? $conditions['Users.pin'] = $where['pin'] : null;
        }

        $result = $this->model->find('all', [
            'order' => 'Students.created DESC',
            'contain' => $return ? [] : [
                'Users' => [
                    'fields' => [
                        'Users.id',
                        'Users.first_name',
                        'Users.last_name',
                        'Users.middle_name',
                        'Users.date_of_birth',
                        'Users.pin',
                        'Users.identity_number',
                        'Users.email'
                    ],
                    'Genders' => [
                        'fields' => [
                            'Genders.name',
                            'Genders.code'
                        ]
                    ],
                    'Identities' => [
                        'IdentityTypes' => [
                            'fields' => [
                                'IdentityTypes.name'
                            ]
                        ],
                        'fields' => [
                            'Identities.security_user_id',
                            'Identities.number'
                        ]
                    ],
                    'Contacts' => [
                        'ContactTypes'
                    ]
                ],
                'Institutions' => [
                    'fields' => [
                        'Institutions.name',
                        'Institutions.code'
                    ]
                ],
                'StudentStatuses' => [
                    'fields' => [
                        'StudentStatuses.name',
                        'StudentStatuses.code'
                    ]
                ]
            ],
            'join' => $return ? [] : [
                [
                    'table' => 'institution_class_students',
                    'alias' => 'ClassStudents',
                    'type' => 'LEFT',
                    'conditions' => 'ClassStudents.student_id = Students.student_id'
                ],
                [
                    'table' => 'institution_classes',
                    'alias' => 'Class',
                    'type' => 'LEFT',
                    'conditions' => 'Class.id = ClassStudents.institution_class_id'
                ],
                [
                    'table' => 'security_users',
                    'alias' => 'ClassHomeroomTeacher',
                    'type' => 'LEFT',
                    'conditions' => 'ClassHomeroomTeacher.id = Class.staff_id'
                ],
                [
                    'table' => 'genders',
                    'alias' => 'ClassHomeroomTeacherGender',
                    'type' => 'LEFT',
                    'conditions' => 'ClassHomeroomTeacherGender.id = ClassHomeroomTeacher.gender_id'
                ],
            ],
            'fields' => $return ? [] : [
                'Students.id',
                'Students.student_id',
                'Students.start_date',
                'Students.end_date',
                'Class.name',
                'Class.capacity',
                'Class.total_male_students',
                'Class.total_female_students',
                'Class.language',
                'ClassHomeroomTeacher.first_name',
                'ClassHomeroomTeacher.last_name',
                'ClassHomeroomTeacher.middle_name',
                'ClassHomeroomTeacher.date_of_birth',
                'ClassHomeroomTeacher.pin',
                'ClassHomeroomTeacherGender.name',
                'ClassHomeroomTeacherGender.code',
            ],
            'conditions' => $conditions
        ]);

        $total = $result->count();
        $result = $all ? $result->all() : $result->limit(30)->page($page);
        $result = $return ? $result : $this->restful->join(
            $result->toArray(), // data array
            'Student.Guardians', // model name to join
            'Guardians', // field name in data array
            [
                'student_id' => '='
            ], // conditions
            [
                'Guardians.id',
                'GuardianRelations.name',
                'Genders.code',
                'Genders.name',
                'Users.first_name',
                'Users.last_name',
                'Users.middle_name',
                'Users.date_of_birth',
                'Users.pin',
                'Users.identity_number',
                'Users.email'
            ], // select array
            [
                'GuardianRelations' => ['Genders'],
                'Users'
            ] // contain array
        );

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', ['data' => $result, 'total' => $total]);
    }

    public function getStudying(array $where)
    {
        $pin = [];
        $fio = [];

        if (isset($where['pin']) && $where['pin']) {
            $pin['Users.pin'] = $where['pin'];
        } else if (
            isset($where['first_name']) && $where['first_name'] &&
            isset($where['last_name']) && $where['last_name'] &&
            isset($where['date_of_birth']) && $where['date_of_birth']
        ) {
            $fio = [
                'Users.first_name' => $where['first_name'],
                'Users.last_name' => $where['last_name'],
                'Users.date_of_birth' => $where['date_of_birth']
            ];
        }

        if (!$pin && !$fio) {
            $this->restful->serialize->offsetSet('result', __('Can not find student'));
        } else {
            $students = $this->model->find()
                ->contain(['Users', 'StudentStatuses'])
                ->where($pin)
                ->orWhere($fio)
                ->all()->toArray();

            $studying = false;
            if ($students) {
                foreach ($students as $student) {
                    if ($student->student_status->code === 'CURRENT') {
                        $studying = true;
                    }
                }
            }

            $this->restful->serialize->offsetSet('result', $studying);
        }
    }

    public function delete(array $where = [])
    {
        $result = [];

        $unknown = array_diff(array_keys($where), $this->model->schema()->columns());
        if ($unknown) {
            foreach ($unknown as $column) {
                unset($where[$column]);
            }
        }

        if ($where) {
            $result = $this->model->deleteAll($where);
        }

        return $result;
    }

    public function addStudentToInstiution(int $studentId, string $studentStartDate, int $institutionId, int $class_id)
    {
        $data = $this->request->data;
        $result = [];

        if ($studentId && $institutionId && $class_id && $data) {
            $student = $this->get([
                'student_id' => $studentId,
                'institution_id' => $institutionId,
                'academic_period_id' => $data['academic_period_id']
            ]);

            if ($student) {
                $periodExist = false;
                $studentStartDateRequest = new Date($studentStartDate);
                foreach ($student as $item) {
                    if (
                        !$item->end_date ||
                        $item->end_date >= $studentStartDateRequest ||
                        $item->start_date >= $studentStartDateRequest
                    ) {
                        $periodExist = true;
                    }
                }

                if ($periodExist) {
                    return __('This student already exist');
                }
            }

            $this->restful->model = $this->model;
            $this->request->data['student_id'] = $studentId;
            $this->request->data['student_status_id'] = 1;
            $this->request->data['institution_id'] = $institutionId;
            $this->request->data['class'] = $class_id;
            $result = $this->restful->add(false);
        }

        return $result;
    }
}
