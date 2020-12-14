<?php
namespace MonAPI\Controller\Component;

use Cake\Event\Event;
use Cake\Controller\Component;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\Date;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class InstitutionOpenDataComponent extends Component
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
        $this->model = $this->restful->instantiateModel('Institution.Institutions');
    }

    public function startup(Event $event)
    {
        $this->controller->set('model', 'Institution.Institutions');
        $this->controller->set('error', null);
    }

    public function hasAccess(?string $code)
    {
        if (!$code) {
            return false;
        }

        $institutions = $this->get(['code' => $code]);
        $institutionTypeId = isset($institutions[0]) ? $institutions[0]->get('institution_type_id') : '';
        $institutionsTypes = $this->controller->Restful->accessParams['institutionsTypesIds'];
        $institutionsCodes = explode(', ', $this->controller->Restful->accessParams['institutionsCodes']);

        if (in_array($code, $institutionsCodes) || in_array('*', $institutionsCodes) && in_array($institutionTypeId, $institutionsTypes)) {
            return true;
        }

        return false;
    }


    public function get(array $where = [], bool $return = true)
    {
        $curDate = date('Y-m-d');
        $InstitutionStudents = TableRegistry::get('Institution.Students');
        $InstitutionStaff = TableRegistry::get('Institution.Staff');
        $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
        $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
        $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
        $statuses = $StudentStatuses->findCodeList();
        $InstitutionClassStudents = TableRegistry::get('Institution.InstitutionClassStudents');
        $EducationFormOfTraining = TableRegistry::get('Education.EducationFormOfTraining');
        $formOfTrainingOptions = $EducationFormOfTraining->getListWithCode();
        $Users = TableRegistry::get('User.Users');
        $genderOptions = $Users->Genders->getList();
        $AcademicPeriod = TableRegistry::get('AcademicPeriod.AcademicPeriods');
        $currentAcademPeriodId = $AcademicPeriod->getCurrent();
        $AcademicPeriodObject = $AcademicPeriod->get($currentAcademPeriodId);
        $previous_academic_period_id = $AcademicPeriodObject->previous_academic_period_id;

        $InstitutionTypes = TableRegistry::get('Institution.Types');
        $InstitutionPeriod = TableRegistry::get('Institution.InstitutionPeriod');
        $allInstitutionTypes = $this->getAllInstitutionTypes($InstitutionTypes);
        $institutionTypesByPeriod = $this->getInstitutionTypesByPeriod($InstitutionPeriod, $allInstitutionTypes, $curDate);
        $InstitutionAttendance = TableRegistry::get('Institution.InstitutionAttendance');
        $InstitutionAggregatedDataVpo = TableRegistry::get('Institution.InstitutionAggregatedDataVpo');
        $InstitutionAdditionallyReports = TableRegistry::get('Institution.InstitutionAdditionallyReports');
        $InstitutionSubjectStaff = TableRegistry::get('Institution.InstitutionSubjectStaff');

        $all = isset($where['_all']) && $where['_all'] ? true : false;
        $page = isset($where['_page']) && (int)$where['_page'] > 0 ? (int)$where['_page'] : 1;

        $conditions = [];

        isset($where['created_from']) && $where['created_from'] ? $conditions['Institutions.created >='] = $where['created_from'] : null;
        isset($where['created_to']) && $where['created_to'] ? $conditions['Institutions.created <='] = $where['created_to'] : null;
        isset($where['code']) && $where['code'] ? $conditions['Institutions.code'] = $where['code'] : null;
        isset($where['institution_type_id']) && $where['institution_type_id'] ? $conditions['Institutions.institution_type_id'] = $where['institution_type_id'] : null;

        $result = $this->model->find('all', [
            'order' => 'Institutions.created DESC',
            'contain' => $return ? [] : ['Types', 'Areas', 'AreaAdministratives', 'Sectors'],
            'fields' => $return ? [] : [
                'Institutions.id',
                'Institutions.name',
                'Institutions.institution_status_id',
                'Institutions.project_capacity',
                'Institutions.design_capacity',
                'Institutions.short_name',
                'Institutions.telephone',
                'Institutions.latitude',
                'Institutions.longitude',
                'Institutions.code',
                'Institutions.classification',
                'Institutions.date_opened',
                'Institutions.institution_type_id',
                'Areas.code',
                'Areas.name',
                'AreaAdministratives.code',
                'AreaAdministratives.name',
                'Sectors.name',
                'Types.international_code'
            ],
            'conditions' => $conditions
        ]);

        $total = $result->count();
        $result = $all ? $result->all() : $result->limit(30)->page($page);
        $result = $result->toArray();


        foreach($result as $keyI=>$itemI){

            $institutionId = $itemI->id;
            $international_code_type = $itemI->type->international_code;
            $institution_type_id = $itemI->institution_type_id;
            // Get Check
            $is_check_graduated_enrolled_student = $this->getCheckGraduatedEnrolledStudent($international_code_type);
            // Staff
            $itemI['aggregated_data_staff'] = $this->getDataStaff($InstitutionStaff, $institutionId, $assignedStatus, $AcademicPeriodObject, $genderOptions);
            // Student
            $itemI['aggregated_data_student'] = $this->getDataStudent($InstitutionStudents, $InstitutionClassStudents, $statuses, $formOfTrainingOptions, $institutionId,
                                                                      $currentAcademPeriodId, $international_code_type, $genderOptions);
            // Graduated
            $itemI['aggregated_data_student_graduated'] = $this->getDataStudentGraduated($InstitutionStudents, $statuses, $institutionId, $previous_academic_period_id,
                                                                                         $genderOptions, $is_check_graduated_enrolled_student);
            // Enrolled
            $itemI['aggregated_data_student_enrolled'] = $this->getDataStudentEnrolled($InstitutionStudents, $statuses, $institutionId, $currentAcademPeriodId,
                                                                                       $genderOptions, $is_check_graduated_enrolled_student);
            // MTB
            $itemI['aggregated_data_mtb'] = $this->getMtb($institutionId, $currentAcademPeriodId);
            //$InfrastructureUtilityInternets
            $itemI['aggregated_data_infrastructure_internet'] = $this->getInfrastructureInternet($institutionId, $currentAcademPeriodId);
            // Assets
            $itemI['aggregated_data_assets'] = $this->getAssets($institutionId, $currentAcademPeriodId);
            // Entrance
            $data_entrance = $this->getEntrance($institutionId, $currentAcademPeriodId);
            $itemI['aggregated_data_entrance'] = $data_entrance;
            // Expenditure
            $itemI['aggregated_data_expenditure'] = $this->getExpenditure($institutionId, $currentAcademPeriodId, $data_entrance);
            $itemI['aggregated_professions_name'] = $this->getProfessionsName($institutionId);
            $itemI['aggregated_area'] = $this->getAreaInfo($institutionId, $currentAcademPeriodId);
            $itemI['leader_name'] = $this->getLeaderFullName($institutionId);
            $itemI['aggregated_withDrawn_departed'] = $this->getWithDrawnDeparted($InstitutionStudents, $institutionId, $statuses, $currentAcademPeriodId, $genderOptions);
            // Attendance
            $itemI['aggregated_attendance'] = $this->getAttendance($InstitutionAttendance, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id);
            $itemI['aggregated_attendance_all'] = $this->getAttendanceAll($InstitutionAttendance, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id);
            // Perfomance
            $itemI['aggregated_perfomance'] = $this->getPerfomance($InstitutionAggregatedDataVpo, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id);
            $itemI['aggregated_hot_food'] = $this->getHotFood($InstitutionAdditionallyReports, $institutionId, $currentAcademPeriodId, $international_code_type);
            $itemI['aggregated_subject_by_staff'] = $this->getSubjectsByStaff($InstitutionSubjectStaff, $institutionId, $currentAcademPeriodId, $international_code_type, $assignedStatus, $genderOptions);
            $result[$keyI] = $itemI;
        }

        if ($return) return $result;

        $this->restful->serialize->offsetSet('Result', ['data' => $result, 'total' => $total]);
    }

    function getSubjectsByStaff($InstitutionSubjectStaff, $institutionId, $currentAcademPeriodId, $international_code_type, $assignedStatus, $genderOptions){
        $_conditions = [
            $InstitutionSubjectStaff->aliasField('institution_id') => $institutionId,
            'InstitutionSubjects.institution_id' => $institutionId,
            'InstitutionSubjects.academic_period_id' => $currentAcademPeriodId,
            'InstitutionStaff.staff_status_id' => $assignedStatus
        ];

        $dataSet = [];
        if($international_code_type == 'PRIMARY SECONDARY'){
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
            if(count($subjectArray) > 0){
                $gendersValue = [];
                foreach ($genderOptions as $key => $value){
                    $gendersValue[$value] = 0;
                }
                $gendersValue['name'] = '';
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
        }
        return $dataSet;
    }

    function getHotFood($InstitutionAdditionallyReports, $institutionId, $currentAcademPeriodId, $international_code_type){
        $hotFood = 0;
        if($international_code_type == 'PRIMARY SECONDARY'){
            $_conditions = [
                $InstitutionAdditionallyReports->aliasField('institution_id') => $institutionId,
                $InstitutionAdditionallyReports->aliasField('academic_period_id') => $currentAcademPeriodId
            ];
            $query = $InstitutionAdditionallyReports->find();
            $InstitutionAdditionallyReports = $query
                ->select([
                    $InstitutionAdditionallyReports->aliasField('stat_students_number_meals_provided_from_the_budget'),
                    $InstitutionAdditionallyReports->aliasField('stat_students_number_meals_provided_from_the_parents')
                ])
                ->where($_conditions)
                ->first();
            if(count($InstitutionAdditionallyReports) > 0){
                $hotFoodBudget = $InstitutionAdditionallyReports->stat_students_number_meals_provided_from_the_budget;
                if(!$hotFoodBudget)
                    $hotFoodBudget = 0;
                $hotFoodParents = $InstitutionAdditionallyReports->stat_students_number_meals_provided_from_the_parents;
                if(!$hotFoodParents)
                    $hotFoodParents = 0;
                $hotFood = $hotFoodBudget + $hotFoodParents;
            }
        }
        return $hotFood;
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

    function getPerfomance($InstitutionAggregatedDataVpo, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id){
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
                return $this->putDataSetByPerfomance($institution_type_id, $aggregatedDataVpoRecord);
            }
        }
        return [];
    }

    function putDataSetByPerfomance($institution_type_id, $aggregatedDataVpoRecord){
        switch ($institution_type_id){
            case 2:
                $dataSet = ['per_excellent_2_4' => $aggregatedDataVpoRecord->od_excellent, 'per_drummers_2_4' => $aggregatedDataVpoRecord->od_good_excellent, 'per_critics_2_4' => $aggregatedDataVpoRecord->od_good_excellent,
                    'per_excellent_5_9' => $aggregatedDataVpoRecord->od_excellent_5_9, 'per_drummers_5_9' => $aggregatedDataVpoRecord->od_good_excellent_5_9, 'per_critics_5_9' => $aggregatedDataVpoRecord->od_satisfactorily_5_9,
                    'per_excellent_10_11' => $aggregatedDataVpoRecord->od_excellent_10_11, 'per_drummers_10_11' => $aggregatedDataVpoRecord->od_good_excellent_10_11, 'per_critics_10_11' => $aggregatedDataVpoRecord->od_satisfactorily_10_11,
                    'main_test' => $aggregatedDataVpoRecord->main_test, 'maths' => $aggregatedDataVpoRecord->maths, 'physics' => $aggregatedDataVpoRecord->physics, 'english' => $aggregatedDataVpoRecord->english,
                    'chemistry' => $aggregatedDataVpoRecord->chemistry, 'biology' => $aggregatedDataVpoRecord->biology, 'history' => $aggregatedDataVpoRecord->history, 'number_of_participants_by_district' => $aggregatedDataVpoRecord->number_of_participants_by_district,
                    'number_of_participants_by_region'=>$aggregatedDataVpoRecord->number_of_participants_by_region, 'republic_number_of_participants'=>$aggregatedDataVpoRecord->republic_number_of_participants,
                    'number_of_participants_international'=>$aggregatedDataVpoRecord->number_of_participants_international, 'district_number_of_winners'=>$aggregatedDataVpoRecord->district_number_of_winners,
                    'region_number_of_winners'=>$aggregatedDataVpoRecord->region_number_of_winners, 'republic_number_of_winners'=>$aggregatedDataVpoRecord->republic_number_of_winners,
                    'international_olympiads_number_of_winners'=>$aggregatedDataVpoRecord->international_olympiads_number_of_winners];
                break;
            default:
                $dataSet = ['absolute_academic_performance' => $aggregatedDataVpoRecord->absolute_academic_performance, 'quality_academic_performance' => $aggregatedDataVpoRecord->quality_academic_performance];
        }
        return $dataSet;
    }

    function getAttendance($InstitutionAttendance, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id){
        $periodId = $institutionTypesByPeriod[$institution_type_id];
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
                    return $attendance_percentage;
            }
        }
        return 0;
    }

    function getAttendanceAll($InstitutionAttendance, $institutionId, $currentAcademPeriodId, $institutionTypesByPeriod, $institution_type_id){
        $periodId = $institutionTypesByPeriod[$institution_type_id];
        if($periodId) {
            $_conditions = [
                $InstitutionAttendance->aliasField('institution_id') => $institutionId,
                $InstitutionAttendance->aliasField('academic_period_id') => $currentAcademPeriodId,
                $InstitutionAttendance->aliasField('institution_period_id') => $periodId
            ];
            $query = $InstitutionAttendance->find();
            $attendanceRecord = $query
                ->contain([
                    'InstitutionPeriod'
                ])
                ->where($_conditions)
                ->first();
            if (count($attendanceRecord) > 0) {
                return ['attendance_percentage' => $attendanceRecord->attendance_percentage, 'percentage_repeated_year_left' => $attendanceRecord->percentage_repeated_year_left,
                        'percentage_of_deducted' => $attendanceRecord->percentage_of_deducted, 'percentage_who_missed_more_than_25_education_days' => $attendanceRecord->percentage_who_missed_more_than_25_education_days];
            }
        }
        return [];
    }

    function getCheckGraduatedEnrolledStudent($international_code_type){
        $is_check = false;
        if($international_code_type != 'PRESCHOOL EDUCATIONAL ORGANIZATION' && $international_code_type != 'CHILDREN EDUCATIONAL CENTERS'
           && $international_code_type != 'PRIMARY SECONDARY'){
            $is_check = true;
        }
        return $is_check;
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

    function getLeaderFullName($institutionId){
        $InstitutionContactPersons = TableRegistry::get('Institution.InstitutionContactPersons');
        $query = $InstitutionContactPersons->find();
        $params = [$InstitutionContactPersons->aliasField('institution_id') => $institutionId, $InstitutionContactPersons->aliasField('preferred') => 1];
        $params[]  = "(StaffPositionTitles.international_code = 'DIRECTOR' OR StaffPositionTitles.international_code = 'RECTOR' OR StaffPositionTitles.international_code = 'MGR')";
        $contactsArray = $query
            ->select([
                $InstitutionContactPersons->aliasField('contact_person')
            ])
            ->contain([
                'StaffPositionTitles'
            ])
            ->where($params)
            ->first()
        ;
        if(count($contactsArray) > 0){
            return $contactsArray->contact_person;
        }
        return '';
    }

    function getAreaInfo($institutionId, $currentAcademPeriodId){
        $InstitutionLands = TableRegistry::get('Institution.InstitutionLands');
        $InstitutionFloorsTable = TableRegistry::get('Institution.InstitutionFloors');

        $institutionLandsConditions = [
            $InstitutionLands->aliasField('institution_id') => $institutionId,
            $InstitutionLands->aliasField('academic_period_id') => $currentAcademPeriodId,
            $InstitutionLands->aliasField('land_status_id') => 1,
            'InstitutionBuildings.building_status_id' => 1,
            'InstitutionBuildings.institution_id' => $institutionId,
            'InstitutionBuildings.academic_period_id' => $currentAcademPeriodId
        ];
        $query = $InstitutionLands->find();

        $institutionLandsArray = $query
            ->select([
                $InstitutionLands->aliasField('name'),
                'InstitutionBuildings.id',
                'InstitutionBuildings.name',
                'InstitutionBuildings.area',
                'InstitutionBuildings.number_of_places_in_the_hostel',
                'BuildingTypes.international_code',
                'InfrastructureConditions.name',
                'InfrastructureConditions.international_code',
                'InfrastructureOwnerships.international_code'
            ])
            ->join(['table'=> 'institution_buildings', 'alias' => 'InstitutionBuildings', 'type' => 'LEFT', 'conditions' => 'InstitutionBuildings.institution_land_id = ' . $InstitutionLands->aliasField('id')])
            ->join(['table'=> 'building_types', 'alias' => 'BuildingTypes', 'type' => 'LEFT', 'conditions' => 'BuildingTypes.id = InstitutionBuildings.building_type_id'])
            ->join(['table'=> 'infrastructure_conditions', 'alias' => 'InfrastructureConditions', 'type' => 'LEFT', 'conditions' => 'InfrastructureConditions.id = InstitutionBuildings.infrastructure_condition_id'])
            ->join(['table'=> 'infrastructure_ownerships', 'alias' => 'InfrastructureOwnerships', 'type' => 'LEFT', 'conditions' => 'InfrastructureOwnerships.id = InstitutionBuildings.infrastructure_ownership_id'])
            ->where($institutionLandsConditions)
            ->toArray()
        ;

        $areasData = [];
        if(count($institutionLandsArray) > 0){
            $technical_condition_building = '';
            $areaTotal = 0;
            $educationalArea = 0;
            $nonEducationalArea = 0;
            $areaRent = 0;
            $number_beds = 0;
            foreach ($institutionLandsArray as $keyL=>$itemL){
                $institutionBuildingId = $itemL->InstitutionBuildings['id'];
                $institutionBuildingName = $itemL->InstitutionBuildings['name'];
                $buildingTypesInternationalCode = $itemL->BuildingTypes['international_code'];
                $ownershipsInternationalCode = $itemL->InfrastructureOwnerships['international_code'];
                $number_of_places_in_the_hostel = $itemL->InstitutionBuildings['number_of_places_in_the_hostel'];

                $area = $itemL->InstitutionBuildings['area'];
                // Кабинеты
                $queryFloor = $InstitutionFloorsTable->find();
                $institutionFloorsConditions = [
                    $InstitutionFloorsTable->aliasField('institution_id') => $institutionId,
                    $InstitutionFloorsTable->aliasField('academic_period_id') => $currentAcademPeriodId,
                    $InstitutionFloorsTable->aliasField('floor_status_id') => 1,
                    $InstitutionFloorsTable->aliasField('institution_building_id') => $institutionBuildingId,
                    'InstitutionRooms.room_status_id' => 1,
                    'InstitutionRooms.institution_id' => $institutionId,
                    'InstitutionRooms.academic_period_id' => $currentAcademPeriodId
                ];
                $institutionFloorsArray = $queryFloor
                    ->select([
                        'InstitutionRooms.area',
                        'RoomTypes.name',
                        'RoomTypes.classification'
                    ])
                    ->join(['table'=> 'institution_rooms', 'alias' => 'InstitutionRooms', 'type' => 'LEFT', 'conditions' => 'InstitutionRooms.institution_floor_id = ' . $InstitutionFloorsTable->aliasField('id')])
                    ->join(['table'=> 'room_types', 'alias' => 'RoomTypes', 'type' => 'LEFT', 'conditions' => 'RoomTypes.id = InstitutionRooms.room_type_id'])
                    ->where($institutionFloorsConditions)
                    ->toArray()
                ;

                if(count($institutionFloorsArray) > 0){
                    foreach($institutionFloorsArray as $keyF=>$itemF){
                        $classification = $itemF->RoomTypes['classification'];
                        $roomArea = $itemF->InstitutionRooms['area'];

                        if ($classification) {
                            $educationalArea += $roomArea;
                        } else {
                            $nonEducationalArea += $roomArea;
                        }
                    }
                }
                $areaTotal += $area;
                // Аренда
                if($ownershipsInternationalCode == 'LEASED'){
                    $areaRent += $area;
                }
                // Общежитие
                if($buildingTypesInternationalCode == 'HOSTEL'){
                    $number_beds += $number_of_places_in_the_hostel;
                }
                // Тех состояние здания
                $infrastructureConditionName = $itemL->InfrastructureConditions['name'];
                $infrastructureCondition = __($infrastructureConditionName);
                $technical_condition_building .= ';' . $institutionBuildingName . ' - ' . $infrastructureCondition;
            }
            $technical_condition_building = substr($technical_condition_building, 1);
            $areasData['area'] = $areaTotal;
            $areasData['technical_condition_building'] = $technical_condition_building;
            $areasData['educational_area'] = $educationalArea;
            $areasData['non_educational_area'] = $nonEducationalArea;
            $areasData['area_rent'] = $areaRent;
            $areasData['number_beds'] = $number_beds;
        }
        return $areasData;
    }

    function getProfessionsName($institutionId){
        $InstitutionGrades = TableRegistry::get('Institution.InstitutionGrades');
        $institutionGradesConditions = [
            $InstitutionGrades->aliasField('institution_id') => $institutionId,
            $InstitutionGrades->aliasField('end_date').' IS NULL',
        ];
        $query = $InstitutionGrades->find();

        $institutionGradesArray = $query
            ->select([
                'EducationProgrammes.name'
            ])
            ->contain([
                'EducationGrades.EducationProgrammes'
            ])
            ->group([
                'EducationProgrammes.id'
            ])
            ->where($institutionGradesConditions)
            ->toArray()
        ;
        $professionsName = '';
        $professionsList = [];
        if(count($institutionGradesArray) > 0){
            foreach($institutionGradesArray as $keyP=>$itemP){
                $professionName = $itemP->EducationProgrammes->name;
                $professionsList[] = $professionName;
            }
            $professionsName = implode(",", $professionsList);
        }
        return $professionsName;
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
        foreach ($staffArray as $keyStaff => $staffItem){
            $pos_type = $staffItem->pos_type;
            $position_names = explode(',', $staffItem->position_names);
            $position_types = explode(',', $staffItem->position_types);
            $staff_position_title_ids = explode(',', $staffItem->staff_position_title_ids);
            $gender = $staffItem->user->gender->name;
            $positionsArray = $this->getUniquePosition($position_names, $position_types, $staff_position_title_ids);
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
        $dataSet['Positions'] = $allPositions;
        $dataSet['count_staff_scheduled'] = $this->getCountStaffscheduled($institutionId);

        // Experience
        $_conditions['StaffPositionTitles.type'] = 1;
        $query = $InstitutionStaff->find();
        $staffArrayExperience = $query
            ->select([
                $InstitutionStaff->aliasField('institution_id'),
                $InstitutionStaff->aliasField('staff_id'),
                $InstitutionStaff->aliasField('institution_position_id'),
                'UserEmployments.security_user_id',
                'experience' => $query->func()->SUM("TIMESTAMPDIFF(MONTH, UserEmployments.date_from, UserEmployments.date_to)")
            ])
            ->join(['table'=> 'institution_positions', 'alias' => 'InstitutionPositions', 'type' => 'LEFT', 'conditions' => 'InstitutionPositions.id = ' . $InstitutionStaff->aliasField('institution_position_id')])
            ->join(['table'=> 'staff_position_titles', 'alias' => 'StaffPositionTitles', 'type' => 'LEFT', 'conditions' => 'StaffPositionTitles.id = InstitutionPositions.staff_position_title_id'])
            ->join(['table'=> 'user_employments', 'alias' => 'UserEmployments', 'type' => 'LEFT', 'conditions' => 'UserEmployments.security_user_id = ' . $InstitutionStaff->aliasField('staff_id')])
            ->where($_conditions)
            ->group([
                $InstitutionStaff->aliasField('staff_id')
            ])
            ->toArray()
        ;
        $staffExperience = ['0_5' => 0, '5_10' => 0, '11_15' => 0, '15_n' => 0];
        if(count($staffArrayExperience) > 0) {
            foreach ($staffArrayExperience as $keySt => $itemSt) {
                $experience = $itemSt->experience;
                $experience_int = (int)($experience / 12);
                if ($experience_int < 5) {
                    $staffExperience['0_5'] += 1;
                } elseif ($experience_int >= 5 && $experience_int <= 10) {
                    $staffExperience['5_10'] += 1;
                } elseif ($experience_int >= 11 && $experience_int <= 15) {
                    $staffExperience['11_15'] += 1;
                } elseif ($experience_int >= 15) {
                    $staffExperience['15_n'] += 1;
                }
            }
        }
        $dataSet['Experience'] = $staffExperience;

        // Trainings

        $start_date = $AcademicPeriodObject->start_date;
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = $AcademicPeriodObject->end_date;
        $end_date = date('Y-m-d', strtotime($end_date));

        $staffsByTrainingConditions = ["StaffTrainings.completed_date BETWEEN '" . $start_date . "' AND " . "'" . $end_date . "'"];
        $staffsByTrainingConditions = array_merge($staffsByTrainingConditions, $_conditions);

        $staffTrainingCount = 0;
        $query = $InstitutionStaff->find();
        $staffTrainingArray = $query
            ->select([
                'total' => $query->func()->count("DISTINCT " . $InstitutionStaff->aliasField('staff_id'))
            ])
            ->contain([
                'Positions'=>['StaffPositionTitles']
            ])
            ->join(['table'=> 'staff_trainings', 'alias' => 'StaffTrainings', 'type' => 'LEFT', 'conditions' => 'StaffTrainings.staff_id = ' . $InstitutionStaff->aliasField('staff_id')])
            ->where($staffsByTrainingConditions)
            ->toArray()
        ;
        if(count($staffTrainingArray) > 0){
            $staffTrainingCount = $staffTrainingArray[0]->total;
        }
        $dataSet['Training'] = $staffTrainingCount;

        // Appraisals
        $curDate = date('Y-m-d');
        //$begin_date = date('Y-m-d', strtotime("-5 year", strtotime($curDate)));
        $start_date = date('Y-m-d', strtotime("-1 day", strtotime($curDate)));

        $staffsByAppraisalsConditions = ["InstitutionStaffAppraisals.appraisal_period_to BETWEEN '$start_date' - INTERVAL 5 YEAR AND '$start_date'"];
        $staffsByAppraisalsConditions = array_merge($staffsByAppraisalsConditions, $_conditions);
        $staffAppraisalsCount = 0;
        $query = $InstitutionStaff->find();
        $staffAppraisalsArray = $query
            ->select([
                'total' => $query->func()->count("DISTINCT " . $InstitutionStaff->aliasField('staff_id'))
            ])
            ->contain([
                'Positions'=>['StaffPositionTitles']
            ])
            ->join(['table'=> 'institution_staff_appraisals', 'alias' => 'InstitutionStaffAppraisals', 'type' => 'LEFT', 'conditions' => 'InstitutionStaffAppraisals.staff_id = ' . $InstitutionStaff->aliasField('staff_id')])
            ->where($staffsByAppraisalsConditions)
            ->toArray()
        ;

        if(count($staffAppraisalsArray) > 0){
            $staffAppraisalsCount = $staffAppraisalsArray[0]->total;
        }
        $dataSet['Appraisals'] = $staffAppraisalsCount;
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

    function getCountStaffscheduled($institutionId){
        $InstitutionPositions = TableRegistry::get('Institution.InstitutionPositions');
        $_conditions_position = [];
        $_conditions_position['StaffPositionTitles.type'] = 1;
        $_conditions_position[$InstitutionPositions->aliasField('institution_id')] = $institutionId;
        $_conditions_position[$InstitutionPositions->aliasField('status_id')] = 29;

        $query = $InstitutionPositions->find();
        $positionArray = $query
            ->select([
                $InstitutionPositions->aliasField('id'),
                'StaffPositionTitles.type'
            ])
            ->contain([
                'StaffPositionTitles'
            ])
            ->where($_conditions_position)
            ->toArray()
        ;
        return count($positionArray);
    }

    function getDataStudentEnrolled($InstitutionStudents, $statuses, $institutionId, $currentAcademPeriodId,
                                    $genderOptions, $is_check_graduated_enrolled_student){
        $dataSet = [];
        $programmes = [];
        foreach ($genderOptions as $key => $value){
            $dataSet[$value] = array('name' => $value, 'data_disablity' => array(), 'data' => array());
        }
        //if($is_check_graduated_enrolled_student){

            $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $statuses['CURRENT'], $institutionId, $currentAcademPeriodId);
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
            if (count($studentByProgramme) > 0) {
                foreach ($studentByProgramme as $key => $programmeItem) {
                    $transfer_international_code = $programmeItem->transfer_international_code;
                    $programme_id = $programmeItem->programme_id;
                    $programme_name = $programmeItem->programme_name;
                    $disablity_count = $programmeItem->disablity_count;
                    $gender_name = $programmeItem->gender_name;
                    $total = $programmeItem->total;

                    if($transfer_international_code == 'PRIMAL' || $transfer_international_code == 'TRANSFER FROM ANOTHER FORM OF STUDY' || $transfer_international_code == 'TRANSFER FROM ANOTHER SPECIALTY'
                        || $transfer_international_code == 'TRANSFER FROM ANOTHER PAYMENT FORM' || $transfer_international_code == 'TRANSFER FROM ANOTHER ORGANIZATION' || $transfer_international_code == 'RECOVERY'){

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
                        $dataSet[$gender_name]['data_disablity'][$programme_id] += $disablity_count;
                    }
                }
            }
        //}
        $dataSet['Programmes'] = $programmes;
        return $dataSet;
    }

    function getDataStudentGraduated($InstitutionStudents, $statuses, $institutionId, $previous_academic_period_id,
                                     $genderOptions, $is_check_graduated_enrolled_student){
        $dataSet = [];
        $programmes = [];
        foreach ($genderOptions as $key => $value){
            $dataSet[$value] = array('name' => $value, 'data' => array());
        }
        //if($is_check_graduated_enrolled_student) {
            $studentsByGradeConditions = $this->getConditionsByStudent($InstitutionStudents, $statuses['GRADUATED'], $institutionId, $previous_academic_period_id);
            $query = $InstitutionStudents->find();
            $start_date = date('Y-m-d');
            /*$disabilityCase = $query->newExpr()
                ->addCase(
                    $query->newExpr()->add(["'" . $start_date . "' BETWEEN UserPeopleDisabilities.from_disability AND UserPeopleDisabilities.to_disability"]),
                    1,
                    'integer'
                );*/
            $studentByProgramme = $query
                ->select([
                    'programme_id' => 'EducationFieldOfStudies.id',
                    'programme_name' => 'EducationFieldOfStudies.name',
                    'gender_name' => 'Genders.name',
                    //'disablity_count' => $query->func()->count($disabilityCase),
                    'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
                ])
                ->contain([
                    'EducationGrades.EducationProgrammes.EducationFieldOfStudies',
                    'Users.Genders'
                ])
                //->join(['table' => 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
                ->where($studentsByGradeConditions)
                ->group([
                    'EducationFieldOfStudies.id',
                    'Genders.name'
                ])
                ->toArray();
            if (count($studentByProgramme) > 0) {
                foreach ($studentByProgramme as $key => $programmeItem) {
                    $programme_id = $programmeItem->programme_id;
                    $programme_name = $programmeItem->programme_name;
                    $disablity_count = $programmeItem->disablity_count;
                    $gender_name = $programmeItem->gender_name;
                    $total = $programmeItem->total;
                    $programmes[$programme_id] = $programme_name;
                    foreach ($dataSet as $dkey => $dvalue) {
                        if (!array_key_exists($programme_id, $dataSet[$dkey]['data'])) {
                            if (!array_key_exists($programme_id, $dataSet[$dkey]['data'])) {
                                //$dataSet[$dkey]['data_disablity'][$programme_id] = 0;
                                $dataSet[$dkey]['data'][$programme_id] = 0;
                            }
                        }
                    }
                    $dataSet[$gender_name]['data'][$programme_id] += $total;
                    //$dataSet[$gender_name]['data_disablity'][$programme_id] += $disablity_count;
                }
            }
        //}
        $dataSet['Programmes'] = $programmes;
        return $dataSet;
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

        $dataAllowance = [];
        if($international_code_type == 'PRESCHOOL EDUCATIONAL ORGANIZATION' || $international_code_type == 'CHILDREN EDUCATIONAL CENTERS'){
            $gradeArray = ['0_1'=> 0, '1_2'=> 0, '2_3'=> 0, '3_4'=> 0, '4_5'=> 0, '5_6'=> 0, '6_7'=> 0, 'other' => 0];
            foreach ($genderOptions as $key => $value) {
                $dataAllowance[$value] = 0;
                $arrived[$value] = 0;
                foreach ($formOfTrainingOptions as $keyF => $valueF){
                    $dataSet[$value][$valueF] = array('name' => $value, 'data_disablity' => $gradeArray, 'data' => $gradeArray);
                }
            }
            $this->getDataStudentsDooByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataAllowance, $dataSet);
        }else{
            foreach ($genderOptions as $key => $value) {
                $dataAllowance[$value] = 0;
                $arrived[$value] = 0;
                foreach ($formOfTrainingOptions as $keyF => $valueF){
                    $dataSet[$value][$valueF] = array('name' => $value, 'data_disablity' => array(), 'data' => array());
                }
            }
            $this->getDataStudentsOtherByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataAllowance, $dataSet);
        }
        return $dataSet;
    }

    function getDataStudentsOtherByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataAllowance, &$dataSet){
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
            $allowance_count = $studentByGrade->allowance_count;

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
            $classesCapacity[$institutionClassesLanguage_id][$institution_class_id] = $capacity;
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
            $dataAllowance[$gradeGender] += $allowance_count;
            $shifts[$shiftName] += $gradeTotal;
            $formOfPayments[$form_of_payment_international_code] += $gradeTotal;
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
        $dataSet['FormOfPayments'] = $formOfPayments;
        $dataSet['Shifts'] = $shifts;
        $dataSet['Allowance'] = $dataAllowance;
    }

    function getDataStudentsDooByGrades($InstitutionStudents, $studentsByGradeConditions, $arrived, $dataAllowance, &$dataSet){
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
                'EducationGrades.name',
                'ShiftOptions.name',
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
                'allowance_count' => $query->func()->count($allowanceCase),
                'total' => $query->func()->count("DISTINCT " . $InstitutionStudents->aliasField('student_id'))
            ])
            ->contain([
                'Users.Genders',
                'EducationGrades.EducationProgrammes.EducationFormOfTraining'
            ])
            ->join(['table'=> 'user_people_disabilities', 'alias' => 'UserPeopleDisabilities', 'type' => 'LEFT', 'conditions' => 'UserPeopleDisabilities.security_user_id = Students.student_id'])
            ->join(['table'=> 'institution_class_students', 'alias' => 'InstitutionClassStudents', 'type' => 'LEFT', 'conditions' => 'InstitutionClassStudents.student_id = Students.student_id'])
            ->join(['table'=> 'institution_classes', 'alias' => 'InstitutionClasses', 'type' => 'LEFT', 'conditions' => 'InstitutionClasses.id = InstitutionClassStudents.institution_class_id'])
            ->join(['table'=> 'institution_shifts', 'alias' => 'InstitutionShifts', 'type' => 'LEFT', 'conditions' => 'InstitutionShifts.id = InstitutionClasses.institution_shift_id'])
            ->join(['table'=> 'shift_options', 'alias' => 'ShiftOptions', 'type' => 'LEFT', 'conditions' => 'ShiftOptions.id = InstitutionShifts.shift_option_id'])
            ->join(['table'=> 'languages', 'alias' => 'Languages', 'type' => 'LEFT', 'conditions' => 'Languages.id = InstitutionClasses.language_id'])
            ->join(['table'=> 'special_needs_user_allowance', 'alias' => 'SpecialNeedsUserAllowance', 'type' => 'LEFT', 'conditions' => 'SpecialNeedsUserAllowance.security_user_id = Students.student_id'])
            ->where($studentsByGradeConditions)
            ->group([
                'age',
                'InstitutionClassStudents.institution_class_id',
                'InstitutionClasses.institution_shift_id',
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
        $shifts = [];

        foreach ($studentByGrades as $key => $studentByGrade) {
            $gradeGender = $studentByGrade->user->gender->name;
            $shiftName = $studentByGrade->ShiftOptions['name'];
            $gradeTotal = $studentByGrade->total;
            $age = $studentByGrade->age;
            $disablity_count = $studentByGrade->disablity_count;
            $allowance_count = $studentByGrade->allowance_count;

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
            $classesCapacity[$institutionClassesLanguage_id][$institution_class_id] = $capacity;
            $languages[$institutionClassesLanguage_id] = $international_code;

            $i = 0;
            $is_find = false;
            foreach($gradeTempArray as $keyGrade=>$itemGrade){
                if($i == $age){
                    $dataSet[$gradeGender][$form_of_training_name]['data'][$keyGrade] += $gradeTotal;
                    $shifts[$shiftName] += $gradeTotal;
                    $dataSet[$gradeGender][$form_of_training_name]['data_disablity'][$keyGrade] += $disablity_count;
                    $dataAllowance[$gradeGender] += $allowance_count;
                    $is_find = true;
                    break;
                }
                $i ++;
            }
            if(!$is_find){
                $dataSet[$gradeGender][$form_of_training_name]['data']['other'] += $gradeTotal;
                $shifts[$shiftName] += $gradeTotal;
                $dataSet[$gradeGender][$form_of_training_name]['data_disablity']['other'] += $disablity_count;
                $dataAllowance[$gradeGender] += $allowance_count;
            }
        }
        $dataSet['Education_Grades'] = $grades;
        $dataSet['Classes'] = $classes;
        $dataSet['Classes_Capacity'] = $classesCapacity;
        $dataSet['Languages'] = $languages;
        $dataSet['Arrived'] = $arrived;
        $dataSet['FormOfPayments'] = [];
        $dataSet['Shifts'] = $shifts;
        $dataSet['Allowance'] = $dataAllowance;
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

    function getExpenditure($institutionId, $currentAcademPeriodId, $data_entrance){
        $InstitutionExpenditure = TableRegistry::get('Institution.InstitutionExpenditure');
        $expenditureConditions = [
            $InstitutionExpenditure->aliasField('academic_period_id') => $currentAcademPeriodId,
            $InstitutionExpenditure->aliasField('institution_id') => $institutionId
        ];

        $query = $InstitutionExpenditure->find();

        $expenditureArray = $query
            ->select([
                'InstitutionReasonForEntrance.international_code',
                'total' => $query->func()->sum($InstitutionExpenditure->aliasField('amount_in_som'))
            ])
            ->contain([
                'InstitutionReasonForEntrance'
            ])
            ->where($expenditureConditions)
            ->group([
                $InstitutionExpenditure->aliasField('institution_reason_for_entrance_id')
            ])
            ->toArray()
        ;

        $dataExpenditure = [];
        if(count($expenditureArray) > 0){
            foreach ($expenditureArray as $keyExpenditure=>$itemExpenditure){
                $total = $itemExpenditure['total'];
                $international_code = $itemExpenditure['institution_reason_for_entrance']->international_code;
                //$entranceTotalByCode = $this->getEntranceTotalByCode($international_code, $data_entrance);
                //if($entranceTotalByCode){
                    //$total = $entranceTotalByCode - $total;
                //}
                $dataExpenditure[] = ['total' => $total, 'international_code' => $international_code];
            }
        }
        return $dataExpenditure;
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

    function getEntrance($institutionId, $currentAcademPeriodId){
        $InstitutionEntrance = TableRegistry::get('Institution.InstitutionEntrance');
        $entranceConditions = [
            $InstitutionEntrance->aliasField('academic_period_id') => $currentAcademPeriodId,
            $InstitutionEntrance->aliasField('institution_id') => $institutionId
        ];

        $query = $InstitutionEntrance->find();

        $entranceArray = $query
            ->select([
                'InstitutionReasonForEntrance.international_code',
                'total' => $query->func()->sum($InstitutionEntrance->aliasField('amount_in_som'))
            ])
            ->contain([
                'InstitutionReasonForEntrance'
            ])
            ->where($entranceConditions)
            ->group([
                $InstitutionEntrance->aliasField('institution_reason_for_entrance_id')
            ])
            ->toArray()
        ;
        $dataEntrance = [];
        if(count($entranceArray) > 0){
            foreach ($entranceArray as $keyEntrance=>$itemEntrance){
                $total = $itemEntrance['total'];
                $international_code = $itemEntrance['institution_reason_for_entrance']->international_code;
                $dataEntrance[] = ['total' => $total, 'international_code' => $international_code];
            }
        }
        return $dataEntrance;
    }

    function getAssets($institutionId, $currentAcademPeriodId){
        $InstitutionAssets = TableRegistry::get('Institution.InstitutionAssets');
        $assetsConditions = [
            $InstitutionAssets->aliasField('academic_period_id') => $currentAcademPeriodId,
            $InstitutionAssets->aliasField('institution_id') => $institutionId,
            $InstitutionAssets->aliasField('asset_status_id') => 1
        ];
        $query = $InstitutionAssets->find();
        $institutionAssetsArray = $query
            ->select([
                $InstitutionAssets->aliasField('institution_id'),
                'AssetTypes.name',
                'AssetTypes.international_code',
                $InstitutionAssets->aliasField('asset_type_id'),
                'total' => $query->func()->count($InstitutionAssets->aliasField('id'))
            ])
            ->contain([
                'AssetTypes'
            ])
            ->where($assetsConditions)
            ->group([
                $InstitutionAssets->aliasField('asset_type_id')
            ])
            ->toArray()
        ;

        $dataAssets = [];
        if(count($institutionAssetsArray) > 0){
            foreach ($institutionAssetsArray as $keyAsset=>$itemAsset){
                $total = $itemAsset->total;
                $name = $itemAsset->asset_type->name;
                $international_code = $itemAsset->asset_type->international_code;
                $dataAssets[] = ['name'=>$name, 'international_code'=>$international_code, 'total'=>$total];
            }
        }
        return $dataAssets;
    }

    function getInfrastructureInternet($institutionId, $currentAcademPeriodId){
        $InfrastructureUtilityInternets = TableRegistry::get('Institution.InfrastructureUtilityInternets');
        $infInternetConditions = [
            $InfrastructureUtilityInternets->aliasField('academic_period_id') => $currentAcademPeriodId,
            $InfrastructureUtilityInternets->aliasField('institution_id') => $institutionId
        ];
        $query = $InfrastructureUtilityInternets->find();

        $infInternetArray = $query
            ->select([
                'UtilityInternetProvider.international_code',
                'UtilityInternetTypes.international_code',
                'UtilityInternetBandwidths.international_code'
            ])
            ->contain([
                'UtilityInternetProvider','UtilityInternetTypes','UtilityInternetBandwidths'
            ])
            ->where($infInternetConditions)
            ->group([
                $InfrastructureUtilityInternets->aliasField('internet_provider_id'),
                $InfrastructureUtilityInternets->aliasField('utility_internet_type_id')
            ])
            ->toArray()
        ;

        $dataInfInternet = [];
        if(count($infInternetArray) > 0){
            foreach ($infInternetArray as $keyAsset=>$itemInternet){
                $internet_provider_int_code = $itemInternet->UtilityInternetProvider->international_code;
                $internet_types_int_code = $itemInternet->UtilityInternetTypes->international_code;
                $internet_bandwidths_int_code = $itemInternet->UtilityInternetBandwidths->international_code;
                $dataInfInternet[] = ['internet_provider'=>$internet_provider_int_code, 'internet_types'=>$internet_types_int_code, 'internet_bandwidths'=>$internet_bandwidths_int_code];
            }
        }
        return $dataInfInternet;
    }

    function getMtb($institutionId, $currentAcademPeriodId){
        $MonMtb = TableRegistry::get('Institution.MonMtb');
        $monMtbConditions = [
            $MonMtb->aliasField('academic_period_id') => $currentAcademPeriodId,
            $MonMtb->aliasField('institution_id') => $institutionId
        ];
        $query = $MonMtb->find();

        $monMtbArray = $query
            ->select([
                $MonMtb->aliasField('number_of_computers'),
                $MonMtb->aliasField('working_computers'),
                $MonMtb->aliasField('number_of_computers_connected_to_the_internet'),
                $MonMtb->aliasField('number_of_computers_connected_to_the_internet_for_education'),
                $MonMtb->aliasField('printer'),
                $MonMtb->aliasField('xerocopy'),
                $MonMtb->aliasField('scanner'),
                $MonMtb->aliasField('total_number_of_books'),
                $MonMtb->aliasField('books_by_specialty'),
                $MonMtb->aliasField('number_of_projectors')
            ])
            ->where($monMtbConditions)
            ->limit(1)
            ->toArray()
        ;

        $dataMonMtb = [];
        if(count($monMtbArray) > 0){
            foreach ($monMtbArray as $keyMtb=>$itemMtb){
                $number_of_computers = $itemMtb->number_of_computers;
                $working_computers = $itemMtb->working_computers;
                $number_of_projectors = $itemMtb->number_of_projectors;
                $number_of_computers_connected_to_the_internet = $itemMtb->number_of_computers_connected_to_the_internet;
                $number_of_computers_connected_to_the_internet_for_education = $itemMtb->number_of_computers_connected_to_the_internet_for_education;

                $printer = $itemMtb->printer;
                $xerocopy = $itemMtb->xerocopy;
                $scanner = $itemMtb->scanner;
                $total_number_of_books = $itemMtb->total_number_of_books;
                $books_by_specialty = $itemMtb->books_by_specialty;
                $dataMonMtb[] = ['number_of_computers'=> $number_of_computers, 'working_computers'=> $working_computers, 'number_of_computers_connected_to_the_internet'=> $number_of_computers_connected_to_the_internet,
                    'number_of_computers_connected_to_the_internet_for_education'=> $number_of_computers_connected_to_the_internet_for_education,
                    'printer'=> $printer, 'xerocopy'=> $xerocopy, 'scanner' => $scanner, 'total_number_of_books' => $total_number_of_books, 'books_by_specialty' => $books_by_specialty,
                    'number_of_projectors'=> $number_of_projectors];
            }
        }
        return $dataMonMtb;
    }
}
