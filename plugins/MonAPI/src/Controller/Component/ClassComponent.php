<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class ClassComponent extends Component
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
        $this->model = $this->restful->instantiateModel('Institution.InstitutionClasses');
    }

    public function add(int $institutionId, int $educationGradeId)
    {
        $data = $this->request->data;

        if ($this->restful->getIfExists($data)) {
            $shift = $this->getShift($institutionId, $data['academic_period_id']);
            if (!$shift || !isset($data['class_name'])) {
                return __('Can not get shift for class creation. Please set correct academic period id');
            }

            $shiftId = $shift->get('id');
            $educationGrade = $this->getProgramme($institutionId, $educationGradeId);
            $recordExist = $this->model->find()->where([
                'institution_id' => $institutionId,
                'academic_period_id' => $data['academic_period_id'],
                'name' => $data['class_name']
            ])->first();

            if ($recordExist) {
                $capacity = $recordExist->get('capacity');
                $capacityPercent = $capacity * 25 / 100;
                $studentsTotal = $recordExist->get('total_male_students') + $recordExist->get('total_female_students') + $capacityPercent;

                if ($capacity < 100) {
                    $recordExist->capacity = 100;
                    $this->model->save($recordExist);
                }

                if(($capacity + $capacityPercent) < 200 && $capacity <= $studentsTotal) {
                    $recordExist->capacity += $capacityPercent;
                    $this->model->save($recordExist);
                }

                return $recordExist->get('id');

            } else if ($educationGrade) {
                $staffs = $this->restful->instantiateModel('Institution.InstitutionStaff');
                $user = $this->model->staff->find()->where(['pin' => isset($data['class_teacher_pin']) ? $data['class_teacher_pin'] : null])->first();
                $staff = null;

                if ($user) {
                    $staff = $staffs->find()->where(['staff_id' => $user->get('id'), 'institution_id' => $institutionId])->first();
                }

                $this->restful->model = $this->model;
                $this->request->data['capacity'] = 100;
                $this->request->data['class_number'] = 1;
                $this->request->data['name'] = $data['class_name'];
                $this->request->data['language_id'] = isset($data['class_language_id']) ? $data['class_language_id'] : null;
                $this->request->data['staff_id'] = $staff && $user ? $user->get('id') : 0;
                $this->request->data['education_grades'] = [$educationGrade[0]->toArray()];
                $this->request->data['academic_period_id'] = $data['academic_period_id'];
                $this->request->data['institution_id'] = $institutionId;
                $this->request->data['institution_shift_id'] = $shiftId;
                $result = $this->restful->add(false);

                if (isset($result['error']) && empty($result['error'])) {
                    return $result['data']->get('id');
                }

                return __('Can not create class');

            } else {
                return __('Can not get programme for class creation. Please set correct education id');
            }
        }

        return __('Can not create class. Please check all fields for class creation');
    }

    public function getShift(int $institutionId, int $academicPeriodId)
    {
        $academicPeriods = $this->restful->instantiateModel('AcademicPeriod.AcademicPeriods');
        $academicPeriod = $academicPeriods->find()->where(['id' => $academicPeriodId])->first();

        if ($academicPeriod) {
            $shifts = $this->restful->instantiateModel('Institution.InstitutionShifts');
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

    public function getProgramme(int $institutionId, int $educationGradeId)
    {
        $institutions = $this->restful->instantiateModel('Institution.Institutions');
        $grades = $this->restful->instantiateModel('Education.EducationGrades');
        $programs = $institutions->InstitutionGrades;
        $program = $programs->find()->where(['institution_id' => $institutionId, 'education_grade_id' => $educationGradeId])->first();

        if ($program) {
            $grade = $grades->get($educationGradeId);
            return [$grade, $program];
        } else {
            $grade = $grades->find()->where(['id' => $educationGradeId])->first();
            if ($grade) {
                $options = ['extra' => $this->restful->extra];
                $academicPeriods = $this->restful->instantiateModel('AcademicPeriod.AcademicPeriods');
                $academicPeriod = $academicPeriods->find()->where(['id' => $this->request->data['academic_period_id']])->first();
                $institution = $institutions->find()->where(['id' => $institutionId])->first();
                $startDate = date('d-m-Y');
                if ($institution && $academicPeriod) {
                    $startDate = $academicPeriod->get('start_date') > $institution->get('date_opened') ? $academicPeriod->get('start_date') : $institution->get('date_opened');
                }

                $programsData = [
                    'start_date' => $startDate,
                    'programme' => $grade->get('education_programme_id'),
                    'education_grade_id' => $grade->get('id'),
                    'institution_id' => $institutionId
                ];
                $program = $programs->newEntity($programsData, $options);
                $programs->save($program, $options);

                return [$grade, $program];
            }
        }

        return false;
    }

    public function get(array $where = null)
    {
        $result = [];

        if ($where) {
            $result = $this->model->find()->where($where)->all()->toArray();
        }

        return $result;
    }

    public function delete(array $where = null)
    {
        $result = [];

        if ($where) {
            $result = $this->model->deleteAll($where);
        }

        return $result;
    }
}
