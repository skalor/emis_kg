<?php

namespace HiddenField\Model\Repository;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;

class Tables
{
    private $excludedFields = [
        'Institution.Institutions' => [
            'security_group_id',
            'shift_type',
            'contact_person',
            'telephone',
            'fax',
            'email',
            'website',
        ],
        'Institution.InstitutionAttachments' => [
            'file_name',
            'file_content'
        ],
        'Institution.InstitutionPositions' => [
            'is_homeroom',
        ],
        'Institution.InstitutionShifts' => [
            'previous_shift_id',
        ],
        'Institution.InstitutionClasses' => [
            'class_number',
            'staff_id',
            'multigrade',
        ],
        'Institution.InstitutionSubjects' => [
            'no_of_seats',
            'education_grade_id',
            'name',
            'total_male_students',
            'total_female_students'
        ],
        'Institution.Students' => [
            'student_id',
            'education_grade_id',
            'academic_period_id',
            'student_status_id'
        ],
        'Institution.InstitutionAssessments' => [
            'class_number',
            'staff_id',
            'institution_shift_id',
            'capacity'
        ],
        'Institution.Staff' => [
            'security_group_user_id',
            'staff_id',
            'institution_position_id',
            'fte',
        ],
        'Institution.InstitutionFees' => [
            'total',
            'institution_id',
            'academic_period_id',
            'education_grade_id'
        ],
        'Institution.StudentFees' => [
            'institution_id',
            'student_status_id',
            'start_date',
            'end_date',
            'previous_institution_student_id',
            'student_id',
            'education_grade_id',
            'academic_period_id'
        ],
        'Institution.StudentTransferIn' => [
            'student_id',
            'previous_institution_id',
            'previous_academic_period_id',
            'previous_education_grade_id',
            'academic_period_id',
            'education_grade_id',
            'institution_id',
        ],
        'Institution.StudentTransferOut' => [
            'institution_class_id',
        ],
        'Quality.InstitutionQualityVisits' => [
            'file_name',
            'file_content'
        ],
        'Institution.StudentAdmission' => [
            'student_id',
            'institution_id',
            'academic_period_id',
            'education_grade_id',
        ],
        'Institution.WithdrawRequests' => [
            'student_id',
            'institution_id',
            'academic_period_id',
            'education_grade_id',
        ],
        'Institution.StudentWithdraw' => [
            'effective_date',
            'comment',
            'institution_id',
            'academic_period_id',
            'student_id',
            'education_grade_id',
        ],
        'Institution.StaffAccount' => [
            'is_staff',
            'is_student',
            'is_guardian',
            'super_admin',
            'openemis_no'
        ],
        'Institution.UndoStudentStatus' => [
            'student_id',
            'institution_id',
            'start_date',
            'start_year',
            'end_date',
            'end_year',
            'class',
        ],
        'Institution.StaffTransferIn' => [
            'previous_institution_staff_id',
            'previous_staff_type_id',
            'previous_fte',
            'transfer_type',
            'previous_effective_date',
            'previous_institution_id',
            'new_institution_id',
            'staff_id'
        ],
        'Institution.StaffTransferOut' => [
            'fte',
            'previous_institution_staff_id',
            'new_fte',
            'new_staff_type_id',
            'new_institution_position_id',
            'staff_type_id',
            'position_start_date',
            'new_end_date',
        ],
        'Institution.StaffPositionProfiles' => [
            'institution_staff_id',
            'staff_id',
            'FTE',
        ],
        'Institution.StaffUser' => [
            'username',
            'openemis_no',
            'identity_number',
            'nationality_id',
            'identity_type_id',
            'is_staff',
            'is_student',
            'is_guardian',
        ],
        'Institution.InstitutionExaminationStudents' => [
            'education_grade_id',
            'registration_number',
        ],
        'Institution.InstitutionContacts' => [
            'security_group_id',
            'shift_type',
            'contact_person',
        ],
        'Quality.VisitRequests' => [
            'file_name',
            'file_content',
        ],
        'Institution.StudentCompetencies' => [
            'class_number',
            'staff_id',
            'institution_shift_id',
            'capacity',
        ],
        'Cases.InstitutionCases' => [
            'case_number',
            'title',
        ],
        'Institution.ReportCardComments' => [
            'class_number',
            'institution_shift_id',
            'staff_id',
            'capacity',
        ],
        'Institution.ReportCardStatuses' => [
            'next_institution_class_id',
            'academic_period_id',
            'student_status_id',
        ],
        'Counselling.Counsellings' => [
            'file_name',
            'file_content',
        ],
        'Institution.StudentCompetencyComments' => [
            'class_number',
            'staff_id',
            'institution_shift_id',
            'capacity',
        ],
        'Institution.InfrastructureNeeds' => [
            'file_name',
            'file_content',
        ],
        'Institution.StudentOutcomes' => [
            'class_number',
            'staff_id',
            'institution_shift_id',
            'capacity',
        ],
        'Institution.BulkStudentAdmission' => [
            'is_editable',
            'is_removable',
            'name',
            'is_system_defined',
            'category',
            'workflow_id',
        ],
        'Institution.StaffRelease' => [
            'previous_institution_staff_id',
            'previous_start_date',
            'previous_fte',
            'previous_staff_type_id',
        ],
        'Institution.StudentStatusUpdates' => [
            'model_reference'
        ],
        'Institution.StudentUser' => [
            'username',
            'openemis_no',
            'identity_number',
            'nationality_id',
            'identity_type_id',
            'is_staff',
            'is_student',
            'is_guardian',
        ],
        'User.Attachments' => [
            'file_name',
            'file_content',
            'date_on_file'
        ],
        'Student.Guardians' => [
            'student_id',
            'guardian_id',
        ],
        'Student.Programmes' => [
            'previous_institution_student_id',
            'start_year',
            'end_year',
        ],
        'Student.Absences' => [
            'institution_student_absence_day_id'
        ],
        'Student.StudentBehaviours' => [
            'openemis_no',
            'student_id',
            'student_behaviour_category_id'
        ],
        'Student.StudentFees' => [
            'institution_id',
            'academic_period_id',
            'education_grade_id',
        ],
        'Staff.Accounts' => [
            'username',
            'openemis_no',
            'identity_number',
            'nationality_id',
            'identity_type_id',
            'is_staff',
            'is_student',
            'is_guardian',
        ],
        'Health.Healths' => [
            'blood_type',
            'health_insurance'
        ],
        'Health.Allergies' => [
            'severe',
            'health_allergy_type_id',
        ],
        'Health.Consultations' => [
            'health_consultation_type_id'
        ],
        'Health.Families' => [
            'current',
            'health_relationship_id',
            'health_condition_id',
        ],
        'Health.Histories' => [
            'current',
            'health_condition_id'
        ],
        'Health.Immunizations' => [
            'health_immunization_type_id'
        ],
        'Health.Tests' => [
            'health_test_type_id'
        ],
        'Student.GuardianUser' => [
            'username',
            'openemis_no',
            'identity_number',
            'nationality_id',
            'identity_type_id',
            'is_staff',
            'is_student',
            'is_guardian',
        ],
        'Student.Textbooks' => [
            'academic_period_id',
            'institution_id',
            'student_id',
        ],
        'Student.StudentRisks' => [
            'average_risk',
            'student_id',
        ],
        'Student.StudentReportCards' => [
            'principal_comments',
            'homeroom_teacher_comments',
            'file_name',
            'file_content',
            'started_on',
            'completed_on',
            'status',
        ],
        'SpecialNeeds.SpecialNeedsReferrals' => [
            'file_name',
            'file_content',
        ],
        'SpecialNeeds.SpecialNeedsServices' => [
            'file_name',
            'file_content',
        ],
        'SpecialNeeds.SpecialNeedsPlans' => [
            'file_name',
            'file_content',
        ],
        'Student.StudentVisitRequests' => [
            'file_name',
            'file_content',
            'institution_id',
        ],
        'Student.StudentVisits' => [
            'file_name',
            'file_content',
            'institution_id',
        ],
        'Guardian.Accounts' => [
            'username',
            'openemis_no',
            'identity_number',
            'nationality_id',
            'identity_type_id',
            'is_staff',
            'is_student',
            'is_guardian',
        ],
        'Staff.Qualifications' => [
            'qualification_level',
            'file_name',
            'file_content',
        ],
        'Staff.StaffTrainings' => [
            'file_name',
            'file_content',
        ],
        'Staff.StaffClasses' => [
            'institution',
            'institution_id',
            'staff_id',
        ],
        'Staff.StaffSubjects' => [
            'institution',
            'institution_id',
            'staff_id',
        ],
        'Institution.StaffLeave' => [
            'number_of_days',
            'file_name',
            'file_content',
            'full_day',
            'staff_id',
            'academic_period_id',
        ],
        'Staff.EmploymentStatuses' => [
            'file_name',
            'file_content'
        ],
        'Staff.Salaries' => [
            'net_salary',
            'additions',
            'deductions',
        ],
        'Staff.Achievements' => [
            'description',
            'objective',
            'end_date',
            'duration',
            'file_name',
            'file_content',
        ],
        'User.InstitutionStaffAttendanceActivities' => [
            'model',
        ],
        'Student.StudentCompetencies' => [
            'class_number',
            'staff_id',
            'secondary_staff_id',
            'institution_shift_id',
            'capacity',
        ],
        'Schedule.ScheduleTerms' => [
            'academic_period_id'
        ],
    ];
    
    public function getExcludedFields(string $model)//: array
    {
        $modelFields = $this->excludedFields[$model] ?? [];
        
        return array_merge($this->generalExcludedFields, $modelFields);
    }
    
    private $categories = [
        'General' => [
            'Institutions' => [
                'Institution' => 'Institution.Institutions',
                'Attachments' => 'Institution.InstitutionAttachments',
                'Import Institutions' => 'Institution.ImportInstitutions',
                'Contacts - Institution' => 'Institution.InstitutionContacts',
            ],
            'InstitutionHistories' => '',
            'InstitutionCalendars' => [
                'Calendar' => 'Calendars',
            ],
            'InstitutionContactPersons' => '',
        ],
        'Staff' => [
            'Institutions' => [
                'Positions' => 'Institution.InstitutionPositions',
                'Staff' => 'Institution.Staff',
                'Behaviour' => 'Institution.StaffBehaviours',
                'Attendance' => 'Institution.StaffAttendances',
                'Accounts' => 'Institution.StaffAccount',
                'Import Staff Attendances' => 'Institution.ImportStaffAttendances',
                'Staff Transfer In' => 'Institution.StaffTransferIn',
                'Staff Transfer Out' => 'Institution.StaffTransferOut',
                'Change in Staff Assignment' => 'Institution.StaffPositionProfiles',
                'Import Staff' => 'Institution.ImportStaff',
                'Staff Profile' => 'Institution.StaffUser',
                'Account Username' => null,
                'Import Institution Positions' => 'Institution.ImportInstitutionPositions',
                'Staff Release' => 'Institution.StaffRelease',
            ],
            'StaffBehaviourAttachments' => [
                'Institution.StaffBehaviourAttachments'],
        ],
        'Academic' => [
            'Institutions' => [
                'Programmes' => 'Institution.InstitutionGrades',
                'Shifts' => 'Institution.InstitutionShifts',
                'All Classes' => 'Institution.InstitutionClasses',
                'My Classes' => 'Institution.InstitutionClasses',
                'All Subjects' => 'Institution.InstitutionSubjects',
                'My Subjects' => 'Institution.InstitutionSubjects',
                'Textbooks' => 'Institution.InstitutionTextbooks',
                'Import Institution Textbooks' => 'Institution.ImportInstitutionTextbooks',
                'Feeder Outgoing Institutions' => 'Institution.FeederOutgoingInstitutions',
                'Feeder Incoming Institutions' => 'Institution.FeederIncomingInstitutions',
            ],
        ],
        'Students' => [
            'Institutions' => [
                'Promotion' => null,
                'Students' => 'Institution.Students',
                'Behaviour' => 'Institution.StudentBehaviours',
                'Attendance' => 'Institution.StudentAttendances',
                'Assessments' => 'Institution.InstitutionAssessments',
                'Student Transfer In' => 'Institution.StudentTransferIn',
                'Student Transfer Out' => 'Institution.StudentTransferOut',
                'Student Admission' => 'Institution.StudentAdmission',
                'Withdraw Request' => 'Institution.WithdrawRequests',
                'Student Withdraw' => 'Institution.StudentWithdraw',
                'Accounts' => 'Institution.StudentAccount',
                'Import Student Admission' => 'Institution.ImportStudentAdmission',
                'Import Student Attendances' => 'Institution.ImportStudentAttendances',
                'Undo Student Status' => 'Institution.UndoStudentStatus',
                'Student Profile' => 'Institution.StudentUser',
                'Account Username' => null,
                'Competency Results' => 'Institution.StudentCompetencies',
                'Risks' => 'Institution.InstitutionRisks',
                'Competency Comments' => 'Institution.StudentCompetencyComments',
                'Outcome Results' => 'Institution.StudentOutcomes',
                'Import Outcome Results' => 'Institution.ImportOutcomeResults',
                'Import Student Body Masses' => 'Institution.ImportStudentBodyMasses',
                'Bulk Student Admission' => 'Institution.BulkStudentAdmission',
                'Import Competency Results' => 'Institution.ImportCompetencyResults',
                'Import Student Guardians' => 'Institution.ImportStudentGuardians',
                'Student Status Updates' => 'Institution.StudentStatusUpdates',
            ],
            'Counsellings' => '',
            'Student Behaviour Attachments' => 'Institution.StudentBehaviourAttachments'
        ],
        'Details' => [
            'Institutions' => [
                'Infrastructure' => 'Institution.InstitutionInfrastructures',
            ],
            'InfrastructureNeeds' => [
                'Infrastructure Need' => 'Institution.InfrastructureNeeds'
            ],
            'InfrastructureProjects' => [
                'Institution.InfrastructureProjects'
            ],
            'InfrastructureWashWaters' => [
                'Infrastructure WASH Waters' => 'Institution.InfrastructureWashWaters'
            ],
            'InfrastructureUtilityElectricities' => [
                'Infrastructure Utility Electricities' => 'InfrastructureUtilityElectricities'],
            
            'InfrastructureUtilityInternets' => [
                'Infrastructure Utility Internets' => 'Institution.InfrastructureUtilityInternets'
            ],
            'InfrastructureUtilityTelephones' => [
                'Infrastructure Utility Telephones' => 'Institution.InfrastructureUtilityTelephones'
            ],
            'InfrastructureWashWastes' => [
                'Infrastructure WASH Wastes' => 'Institution.InfrastructureWashWastes'
            ],
            'InfrastructureWashSewages' => [
                'Infrastructure WASH Sewages' => 'Institution.InfrastructureWashSewages'
            ],
            'InfrastructureWashSanitations' => [
                'Infrastructure WASH Sanitations' => 'Institution.InfrastructureWashSanitations'
            ],
            'InfrastructureWashHygienes' => [
                'Infrastructure WASH Hygienes' => 'Institution.InfrastructureWashHygienes'
            ],
            'InstitutionAssets' => [
                'Institution Assets' => 'Institution.InstitutionAssets'
            ],
        ],
        'Finance' => [
            'Institutions' => [
                'Bank Accounts' => 'Institution.InstitutionBankAccounts',
                'Fees' => 'Institution.InstitutionFees',
                'Students' => 'Institution.StudentFees',
            ],
        ],
        'Surveys' => [
            'Institutions' => [
                'Import' => 'Institution.ImportInstitutionSurveys',
                'Surveys' => 'Institution.InstitutionSurveys',
            ],
        ],
        'Rubrics' => [
            'Institutions' => [
                'New' => null,
                'Completed' => null,
            ],
        ],
        'Quality' => [
            'Institutions' => [
                'Visits' => 'Quality.InstitutionQualityVisits',
                'Visit Requests' => 'Quality.VisitRequests',
            ],
        ],
        'Examinations' => [
            'Institutions' => [
                'Exams' => 'Institution.InstitutionExaminations',
                'Students' => 'Institution.InstitutionExaminationStudents',
                'Results' => 'Institution.ExaminationResults',
            ],
        ],
        'Cases' => [
            'Institutions' => [
                'Cases' => 'Cases.InstitutionCases',
            ],
        ],
        'Report Cards' => [
            'Institutions' => [
                'Comments' => 'Institution.ReportCardComments',
                'Statuses' => 'Institution.ReportCardStatuses',
                'Generate/Download' => null,
                'Publish/Unpublish' => null,
                'Email/Email All' => null,
            ],
        ],
        'Transport' => [
            'InstitutionTransportProviders' => '',
            'InstitutionBuses' => '',
            'InstitutionTrips' => '',
        ],
        'Students - General' => [
            'Institutions' => [
                'Overview' => 'Institution.StudentUser',
            ],
            'Students' => [
                'Contacts' => 'User.Contacts',
                'Identities' => 'User.Identities',
                'Nationalities' => 'User.UserNationalities',
                'Languages' => 'User.UserLanguages',
                'Attachments' => 'User.Attachments',
                'Accounts' => 'Staff.Accounts',
                'Demographic' => 'User.Demographic',
                'Transport' => 'Student.StudentTransport',
            ],
            'StudentComments' => [
                'Comments' => 'User.Comments',
            ],
            'StudentHistories' => [
                'History' => 'Institution.StudentHistories',
            ],
        ],
        'Students - Academic' => [
            'Students' => [
                'Awards' => 'User.Awards',
                'Classes' => 'Student.StudentClasses',
                'Subjects' => 'Student.StudentSubjects',
                'Absence' => 'Student.Absences',
                'Behaviour' => 'Student.StudentBehaviours',
                'Assessments' => null,
                'Extracurricular' => 'Student.Extracurriculars',
                'Examinations' => null,
                'Report Cards' => 'Student.StudentReportCards',
                'Outcomes' => 'Student.StudentOutcomes',
                'Student Behaviour Attachments' => 'Institution.StudentBehaviourAttachments',
                'Competencies' => 'Student.StudentCompetencies',
            ],
            'Institutions' => [
                'Programmes' => 'Student.Programmes',
                'Textbooks' => 'Student.Textbooks',
                'Risks' => 'Student.StudentRisks',
            ],
        ],
        'Students - Guardians' => [
            'Students' => [
                'Guardian Relation' => 'Student.Guardians',
                'Guardian Profile' => 'Student.GuardianUser',
            ],
            'Guardians' => [
                'Guardian Accounts' => 'Guardian.Accounts',
                'Guardian Identities' => 'User.Identities',
                'Guardian Nationalities' => 'User.UserNationalities',
                'Guardian Contacts' => 'User.Contacts',
                'Guardian Languages' => 'User.UserLanguages',
                'Guardian Attachments' => 'User.Attachments',
                'Guardian Demographic' => 'User.Demographic',
            ],
            'GuardianComments' => [
                'Guardian Comments' => 'User.Comments',
            ],
        ],
        'Students - Finance' => [
            'Students' => [
                'Bank Accounts' => 'User.BankAccounts',
                'Fees' => 'Student.StudentFees',
            ],
        ],
        'Students - Health' => [
            'Students' => [
                'Overview' => 'Health.Healths',
                'Allergies' => 'Health.Allergies',
                'Consultations' => 'Health.Consultations',
                'Families' => 'Health.Families',
                'Histories' => 'Health.Histories',
                'Immunizations' => 'Health.Immunizations',
                'Medications' => 'Health.Medications',
                'Tests' => 'Health.Tests',
            ],
            'StudentBodyMasses' => [
                'Student Body Mass' => 'User.UserBodyMasses'
            ],
            'StudentInsurances' => [
                'Student Insurance' => 'User.UserInsurances'
            ],
        ],
        'Students - Professional' => [
            'StudentsStudents' => [
                'Employment' => 'User.UserEmployments',
            ],
        ],
        'Committees' => [
            'InstitutionCommittees' => '',
            'InstitutionCommitteeAttachments' => '',
        ],
        'Students - Special Needs' => [
            'Students' => [
                'Referrals' => 'SpecialNeeds.SpecialNeedsReferrals',
                'Assessments' => 'SpecialNeeds.SpecialNeedsAssessments',
                'Services' => 'SpecialNeeds.SpecialNeedsServices',
                'Devices' => 'SpecialNeeds.SpecialNeedsDevices',
                'Plans' => 'SpecialNeeds.SpecialNeedsPlans',
            ],
        ],
        'Students - Visits' => [
            'Students' => [
                'Visit Requests' => 'Student.StudentVisitRequests',
                'Visits' => 'Student.StudentVisits',
            ],
        ],
        'Staff - General' => [
            'Institutions' => [
                'Overview' => 'Institution.StaffUser',
            ],
            'Staff' => [
                'Contacts' => 'User.Contacts',
                'Identities' => 'User.Identities',
                'Nationalities' => 'User.UserNationalities',
                'Languages' => 'User.UserLanguages',
                'Attachments' => 'User.Attachments',
                'Accounts' => 'Staff.Accounts',
                'Demographic' => 'User.Demographic',
            ],
            'StaffComments' => [
                'Comments' => 'User.Comments',
            ],
            'StaffHistories' => [
                'History' => 'Institution.StaffHistories'
            ],
        ],
        'Staff - Professional' => [
            'Staff' => [
                'Awards' => 'User.Awards',
                'Qualifications' => 'Staff.Qualifications',
                'Extracurricular' => 'Staff.Extracurriculars',
                'Memberships' => 'Staff.Memberships',
                'Licenses' => 'Staff.Licenses',
                'Employment' => 'User.UserEmployments',
                'Import Staff Qualifications' => 'Staff.ImportStaffQualifications',
            ],
        ],
        'Staff - Training' => [
            'Staff' => [
                'Courses' => 'Staff.StaffTrainings',
                'Achievements' => 'Staff.Achievements',
            ],
            'Institutions' => [
                'Needs' => 'Institution.StaffTrainingNeeds',
                'Results' => 'Institution.StaffTrainingResults',
                'Applications' => 'Institution.StaffTrainingApplications',
            ],
        ],
        'Staff - Career' => [
            'Staff' => [
                'Positions' => 'Staff.Positions',
                'Classes' => 'Staff.StaffClasses',
                'Subjects' => 'Staff.StaffSubjects',
                'Behaviour' => 'Staff.StaffBehaviours',
                'Employment Status' => 'Staff.EmploymentStatuses',
                'Staff Behaviour Attachments' => 'Institution.StaffBehaviourAttachments',
                'Attendances' => 'Institution.StaffAttendances',
                'Attendances Activities' => 'User.InstitutionStaffAttendanceActivities',
            ],
            'Institutions' => [
                'Leave' => 'Institution.StaffLeave',
                'Appraisals' => 'Institution.StaffAppraisals',
                'Import Staff Leave' => 'Institution.ImportStaffLeave',
            ],
        ],
        'Staff - Finance' => [
            'Staff' => [
                'Salary Details' => 'Staff.Salaries',
                'Bank Accounts' => 'User.BankAccounts',
                'Salary List' => 'Staff.Salaries',
                'Import Staff Salaries' => 'Staff.ImportSalaries',
            ],
        ],
        'Staff - Health' => [
            'Staff' => [
                'Overview' => 'Health.Healths',
                'Allergies' => 'Health.Allergies',
                'Consultations' => 'Health.Consultations',
                'Families' => 'Health.Families',
                'Histories' => 'Health.Histories',
                'Immunizations' => 'Health.Immunizations',
                'Medications' => 'Health.Medications',
                'Tests' => 'Health.Tests',
            ],
            'StaffBodyMasses' => [
                'Staff Body Mass' => 'User.UserBodyMasses',
            ],
            'StaffInsurances' => [
                'Staff Insurance' => 'User.UserInsurances',
            ],
        ],
        'Staff - Special Needs' => [
            'Staff' => [
                'Referrals' => 'SpecialNeeds.SpecialNeedsReferrals',
                'Assessments' => 'SpecialNeeds.SpecialNeedsAssessments',
                'Services' => 'SpecialNeeds.SpecialNeedsServices',
                'Devices' => 'SpecialNeeds.SpecialNeedsDevices',
                'Plans' => 'SpecialNeeds.SpecialNeedsPlans',
            ],
        ],
        'Timetable' => [
            'Institutions' => [
                'Students' => null,
                'Staff' => null,
            ],
        ],
        'Schedules' => [
            'Institutions' => [
                'Timetable' => 'Schedule.ScheduleTimetables',
                'Intervals' => 'Schedule.ScheduleIntervals',
                'Terms' => 'Schedule.ScheduleTerms',
            ],
        ],
    ];
    
    private $generalExcludedFields = [
        'id',
        'created_user_id',
        'modified_user_id',
        'modified',
        'created',
        'order',
    ];
    
    private $only = [
        'Institution.InstitutionContacts' => ['telephone', 'fax', 'email', 'website']
    ];
    
    public function only(string $model): ?array
    {
        return $this->only[$model] ?? null;
    }
    
    public function hasModel(string $model): bool
    {
        return $this->search($this->categories, $model);
    }
    
    private function search($arr, string $search): bool
    {
        if(!is_array($arr)) {
            return $arr === $search;
        }
        
        foreach($arr as $key => $item) {
            if($item === '' && $key === explode('.', $search)[1]) {
                return true;
            }
            
            if($this->search($item, $search)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getItem(string $category, string $controller, string $name): ?string
    {
        $item = $this->categories[$category][$controller];
        
        if($item === '') {
            $module = ucfirst(Inflector::singularize(explode('_', Inflector::underscore($controller))[0]));
            return "$module.$controller";
        }
        
        $item = $item[$name];
        
        return $item;
    }
    
    public function isNull(Entity $func): bool
    {
        return $this->getItem($func->category, $func->controller, $func->name) === null;
    }
}