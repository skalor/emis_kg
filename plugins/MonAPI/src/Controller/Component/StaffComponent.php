<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\I18n\Date;

class StaffComponent extends Component
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
        $this->model = $this->restful->instantiateModel('Institution.Staff');
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
                $this->request->data['is_staff'] = 1;
                $result = $this->controller->User->add([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'date_of_birth' => $data['date_of_birth']
                ]);

                if (isset($result['error']) && empty($result['error'])) {
                    if (isset($data['mobile'])) {
                        $result['StaffContact'] = $this->controller->User->addContact($result['data']->get('id'));
                    }

                    $instPos = $this->addInstitutionPosition($institution->get('id'), $result['data']->get('id'));
                    if (isset($data['start_date']) && $instPos) {
                        if (is_string($instPos)) {
                            $result['InstitutionStaff'] = $instPos;
                        } else {
                            $result['InstitutionStaff'] = $this->addStaffToInstitution($result['data']->get('id'), $data['start_date'], $institution->get('id'), $instPos->get('id'));
                        }
                    }
                }
            } else {
                $result = __('Institution not found');
            }
        }

        $this->controller->Restful->logging('institution_staff_' . date('Y-m-d'), $result);

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
                $result = $this->controller->User->update([
                    'id' => $data['id']
                ], $update);

                if (isset($result['error']) && empty($result['error'])) {
                    if (isset($data['mobile'])) {
                        $result['StaffContact'] = $this->controller->User->addContact($result['data']->get('id'), $update);
                    }

                    $instPos = $this->addInstitutionPosition($institution->get('id'), $result['data']->get('id'));
                    if (isset($data['start_date']) && $instPos) {
                        if (is_string($instPos)) {
                            $result['InstitutionStaff'] = $instPos;
                        } else {
                            $result['InstitutionStaff'] = $this->addStaffToInstitution($result['data']->get('id'), $data['start_date'], $institution->get('id'), $instPos->get('id'));
                        }
                    }
                }
            } else {
                $result = __('Institution not found');
            }
        }

        $this->controller->Restful->logging('institution_staff_' . date('Y-m-d'), $result);

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

        isset($where['created_from']) && $where['created_from'] ? $conditions['Staff.created >='] = $where['created_from'] : null;
        isset($where['created_to']) && $where['created_to'] ? $conditions['Staff.created <='] = $where['created_to'] : null;
        isset($where['staff_id']) && $where['staff_id'] ? $conditions['Staff.staff_id'] = $where['staff_id'] : null;
        isset($where['institution_id']) && $where['institution_id'] ? $conditions['Staff.institution_id'] = $where['institution_id'] : null;
        isset($where['institution_position_id']) && $where['institution_position_id'] ? $conditions['Staff.institution_position_id'] = $where['institution_position_id'] : null;
        isset($where['first_name']) && $where['first_name'] ? $conditions['Users.first_name'] = $where['first_name'] : null;
        isset($where['last_name']) && $where['last_name'] ? $conditions['Users.last_name'] = $where['last_name'] : null;
        isset($where['date_of_birth']) && $where['date_of_birth'] ? $conditions['Users.date_of_birth'] = $where['date_of_birth'] : null;
        isset($where['pin']) && $where['pin'] ? $conditions['Users.pin'] = $where['pin'] : null;

        $result = $this->model->find('all', [
            'order' => 'Staff.created DESC',
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
                'Positions' => [
                    'fields' => [
                        'Positions.staff_position_title_id'
                    ]
                ],
                'StaffStatuses' => [
                    'fields' => [
                        'StaffStatuses.name',
                        'StaffStatuses.code'
                    ]
                ]
            ],
            'fields' => $return ? [] : [
                'Staff.id',
                'Staff.start_date',
                'Staff.end_date',
                'Staff.institution_position_id'
            ],
            'conditions' => $conditions
        ]);

        $total = $result->count();
        $result = $all ? $result->all() : $result->limit(30)->page($page);
        $result = $result->toArray();

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', ['data' => $result, 'total' => $total]);
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

    public function addStaffToInstitution(int $staffId, string $staffStartDate, int $institutionId, int $institutionPosId)
    {
        $data = $this->request->data;
        $result = [];

        if ($staffId && $institutionId && $institutionPosId && $data) {
            $staff = $this->get([
                'staff_id' => $staffId,
                'institution_id' => $institutionId,
                'institution_position_id' => $institutionPosId
            ]);

            if ($staff) {
                $periodExist = false;
                $staffStartDateRequest = new Date($staffStartDate);
                foreach ($staff as $item) {
                    if (
                        !$item->end_date ||
                        $item->end_date >= $staffStartDateRequest ||
                        $item->start_date >= $staffStartDateRequest
                    ) {
                        $periodExist = true;
                    }
                }

                if ($periodExist) {
                    return __('This staff already exist');
                }
            }

            $this->restful->model = $this->model;
            $this->request->data['staff_id'] = $staffId;
            $this->request->data['staff_status_id'] = 1;
            $this->request->data['institution_id'] = $institutionId;
            $this->request->data['institution_position_id'] = $institutionPosId;
            $result = $this->restful->add(false);
        }

        return $result;
    }

    public function addInstitutionPosition(int $institutionId, int $staffId)
    {
        $instPosTitleId = $this->request->data['institution_position_title_id'];
        $result = [];

        if ($instPosTitleId && $institutionId) {
            $institutionPositions = $this->restful->instantiateModel('Institution.InstitutionPositions');
            $staffPositionTitlesGrades = $this->restful->instantiateModel('Institution.StaffPositionTitlesGrades');
            $staffPositionTitles = $this->restful->instantiateModel('Institution.StaffPositionTitles');

            $staffPositionTitlesGrade = $staffPositionTitlesGrades->find()->where(['staff_position_title_id' => $instPosTitleId])->first();
            if (!$staffPositionTitlesGrade) {
                return __('Can not find staff position');
            }

            $staffPositionTitle = $staffPositionTitles->get($staffPositionTitlesGrade->get('staff_position_title_id'));

            $institutionPosition = $institutionPositions->find()->where([
                'institution_id' => $institutionId,
                'staff_position_title_id' => $staffPositionTitlesGrade->get('staff_position_title_id'),
                'staff_position_grade_id' => $staffPositionTitlesGrade->get('staff_position_grade_id')
            ])->all()->toArray();

            if ($institutionPosition) {
                $posIds = [];
                foreach ($institutionPosition as $position) {
                    $posIds[] = $position->get('id');
                }

                $staffInPosition = $this->get([
                    'staff_id' => $staffId,
                    'institution_id' => $institutionId
                ]);

                if ($staffInPosition) {
                    foreach ($staffInPosition as $staff) {
                        $findedPosKey = array_search($staff->get('institution_position_id'), $posIds);
                        if ($findedPosKey !== false) {
                            return $institutionPosition[$findedPosKey];
                        }
                    }
                }
            }

            $this->restful->model = $institutionPositions;
            $this->request->data['position_no'] = $institutionPositions->getUniquePositionNo($institutionId);
            $this->request->data['institution_id'] = $institutionId;
            $this->request->data['staff_position_title_id'] = $staffPositionTitlesGrade->get('staff_position_title_id');
            $this->request->data['staff_position_grade_id'] = $staffPositionTitlesGrade->get('staff_position_grade_id');
            $this->request->data['status_id'] = 29;
            $this->request->data['assignee_id'] = 2;

            if ($staffPositionTitle->get('type') && (!isset($this->request->data['is_homeroom']) || !$this->request->data['is_homeroom'])) {
                $this->request->data['is_homeroom'] = 0;
            } else if (!$staffPositionTitle->get('type') && isset($this->request->data['is_homeroom'])) {
                unset($this->request->data['is_homeroom']);
            }

            $position = $this->restful->add(false);
            if (!$position['error']) {
                $result = $position['data'];
            } else {
                $result = __('Staff position creating error');
            }
        }

        return $result;
    }
}
