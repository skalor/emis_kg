<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Network\Request;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use App\Model\Table\AppTable;
use Cake\Network\Session;
use Cake\Core\Configure;
use App\Model\Table\ControllerActionTable;
use Cake\Database\Exception as DatabaseException;
use App\Model\Traits\OptionsTrait;

class StudentUserTable extends ControllerActionTable
{
    use OptionsTrait;
    public function initialize(array $config)
    {
        $this->table('security_users');
        $this->entityClass('User.User');
        parent::initialize($config);

        // Associations
        self::handleAssociations($this);

        // Behaviors
        $this->addBehavior('User.User');
        if (!in_array('Custom Fields', (array) Configure::read('School.excludedPlugins'))) {
            $this->addBehavior('CustomField.Record', [
                'model' => 'Student.Students',
                'behavior' => 'Student',
                'fieldKey' => 'student_custom_field_id',
                'tableColumnKey' => 'student_custom_table_column_id',
                'tableRowKey' => 'student_custom_table_row_id',
                'fieldClass' => ['className' => 'StudentCustomField.StudentCustomFields'],
                'formKey' => 'student_custom_form_id',
                'filterKey' => 'student_custom_filter_id',
                'formFieldClass' => ['className' => 'StudentCustomField.StudentCustomFormsFields'],
                'formFilterClass' => ['className' => 'StudentCustomField.StudentCustomFormsFilters'],
                'recordKey' => 'student_id',
                'fieldValueClass' => ['className' => 'StudentCustomField.StudentCustomFieldValues', 'foreignKey' => 'student_id', 'dependent' => true, 'cascadeCallbacks' => true],
                'tableCellClass' => ['className' => 'StudentCustomField.StudentCustomTableCells', 'foreignKey' => 'student_id', 'dependent' => true, 'cascadeCallbacks' => true, 'saveStrategy' => 'replace']
            ]);
        }

        $this->addBehavior('Excel', [
            'excludes' => ['photo_name', 'is_student', 'is_staff', 'is_guardian', 'super_admin', 'date_of_death'],
            'filename' => 'Students',
            'pages' => ['view']
        ]);

        $this->addBehavior('Institution.UserReferenceGenerate', ['type' => 'student']);

        $this->addBehavior('Configuration.Pull');

        $this->belongsTo('Languages', ['className' => 'Languages', 'foreignKey' => 'language_id']);
        $this->belongsTo('FormOfPayment', ['className' => 'Institution.FormOfPayment', 'foreignKey' => 'form_of_payment_id']);

        $this->addBehavior('TrackActivity', ['target' => 'User.UserActivities', 'key' => 'security_user_id', 'session' => 'Student.Students.id']);
        $this->addBehavior('Restful.RestfulAccessControl', [
            'Students' => ['index', 'add', 'edit']
        ]);
        if (!in_array('Risks', (array)Configure::read('School.excludedPlugins'))) {
            $this->addBehavior('Risk.Risks');
        }

        $this->toggle('index', false);
        $this->toggle('remove', false);
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $options['associated']['Nationalities'] = [
            'validate' => 'AddByAssociation'
        ];
        $options['associated']['Identities'] = [
            'validate' => 'AddByAssociation'
        ];
    }

    public static function handleAssociations($model)
    {
        $model->belongsTo('Genders', ['className' => 'User.Genders']);
        $model->belongsTo('AddressAreas', ['className' => 'Area.AreaAdministratives', 'foreignKey' => 'address_area_id']);
        $model->belongsTo('BirthplaceAreas', ['className' => 'Area.AreaAdministratives', 'foreignKey' => 'birthplace_area_id']);
        $model->belongsTo('MainNationalities', ['className' => 'FieldOption.Nationalities', 'foreignKey' => 'nationality_id']);
        $model->belongsTo('MainIdentityTypes', ['className' => 'FieldOption.IdentityTypes', 'foreignKey' => 'identity_type_id']);
        $model->belongsTo('NationalitiesUsers', ['className' => 'FieldOption.NationalitiesUsers', 'foreignKey' => 'nationality_user_id']);
        $model->belongsTo('FormOfStudy', ['className' => 'FieldOption.FormOfStudy', 'foreignKey' => 'form_of_study_id']);

        $model->hasMany('Identities', ['className' => 'User.Identities',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Nationalities', ['className' => 'User.UserNationalities',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Contacts', ['className' => 'User.Contacts',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Attachments', ['className' => 'User.Attachments',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('BankAccounts', ['className' => 'User.BankAccounts',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Comments', ['className' => 'User.Comments',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Languages', ['className' => 'User.UserLanguages',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Awards', ['className' => 'User.Awards',            'foreignKey' => 'security_user_id', 'dependent' => true]);

        $model->hasMany('SpecialNeeds', ['className' => 'SpecialNeeds.SpecialNeedsAssessments',    'foreignKey' => 'security_user_id', 'dependent' => true]);

        $model->belongsToMany('SecurityRoles', [
            'className' => 'Security.SecurityRoles',
            'foreignKey' => 'security_role_id',
            'targetForeignKey' => 'security_user_id',
            'through' => 'Security.SecurityGroupUsers',
            'dependent' => true
        ]);

        $model->hasMany('ClassStudents', [
            'className' => 'Institution.InstitutionClassStudents',
            'foreignKey' => 'student_id'
        ]);

        // remove all student records from institution_students, institution_site_student_absences, student_behaviours, assessment_item_results, student_guardians, institution_student_admission, student_custom_field_values, student_custom_table_cells, student_fees, student_extracurriculars


        $model->belongsToMany('Institutions', [
            'className' => 'Institution.Institutions',
            'joinTable' => 'institution_students',
            'foreignKey' => 'student_id',
            'targetForeignKey' => 'institution_id',
            'through' => 'Institution.Students',
            'dependent' => true
        ]);

        $model->hasMany('InstitutionStudents', ['className' => 'Institution.Students',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->hasMany('InstitutionStaff', ['className' => 'Institution.Staff',    'foreignKey' => 'staff_id', 'dependent' => true]);
        $model->hasMany('StudentAbsences', ['className' => 'Institution.InstitutionSiteStudentAbsences',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('StudentBehaviours', ['className' => 'Institution.StudentBehaviours',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->hasMany('AssessmentItemResults', ['className' => 'Assessment.AssessmentItemResults',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->belongsToMany('Guardians', [
            'className' => 'Student.Guardians',
            'foreignKey' => 'student_id',
            'targetForeignKey' => 'guardian_id',
            'through' => 'Student.StudentGuardians',
            'dependent' => true
        ]);
        $model->hasMany('StudentAdmission', ['className' => 'Institution.StudentAdmission',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->hasMany('StudentCustomFieldValues', ['className' => 'CustomField.StudentCustomFieldValues',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('StudentCustomTableCells', ['className' => 'CustomField.StudentCustomTableCells',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('StudentFees', ['className' => 'Institution.StudentFeesAbstract',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->hasMany('Extracurriculars', ['className' => 'Student.Extracurriculars',    'foreignKey' => 'security_user_id', 'dependent' => true]);
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Model.Students.afterSave'] = 'studentsAfterSave';
        $events['ControllerAction.Model.pull.beforePatch'] = 'pullBeforePatch';
        return $events;
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $BaseUsers = TableRegistry::get('User.Users');
        $validator = $BaseUsers->setUserValidation($validator, $this);
        $validator
            ->allowEmpty('student_name')
            ->add('student_name', 'ruleStudentNotEnrolledInAnyInstitutionAndSameEducationSystem', [
                'rule' => ['studentNotEnrolledInAnyInstitutionAndSameEducationSystem', []],
                'on' => 'create',
                'last' => true
            ])
            ->add('student_name', 'ruleStudentNotCompletedGrade', [
                'rule' => ['studentNotCompletedGrade', []],
                'on' => 'create',
                'last' => true
            ])
            ->add('student_name', 'ruleCheckAdmissionAgeWithEducationCycleGrade', [
                'rule' => ['checkAdmissionAgeWithEducationCycleGrade'],
                'on' => 'create'
            ])
            ->allowEmpty('class')
            ->requirePresence('institution_class_id', 'create')
            ->allowEmpty('institution_reason_for_transfer_id', 'create')
            ->allowEmpty('language_id')
            ->add('class', 'ruleClassMaxLimit', [
                'rule' => ['checkInstitutionClassMaxLimit'],
                'on' => function ($context) {
                    return (!empty($context['data']['class']) && $context['newRecord']);
                }
            ])
            ->add('date_of_birth', 'ruleCheckAdmissionAgeWithEducationCycleGrade', [
                'rule' => ['checkAdmissionAgeWithEducationCycleGrade'],
                'on' => 'create'
            ])
            ->add('gender_id', 'ruleCompareStudentGenderWithInstitution', [
                'rule' => ['compareStudentGenderWithInstitution']
            ])
            ->requirePresence('start_date', 'create')
            ->add('start_date', 'ruleCheckProgrammeEndDateAgainstStudentStartDate', [
                'rule' => ['checkProgrammeEndDateAgainstStudentStartDate', 'start_date'],
                'on' => 'create'
            ])
            ->requirePresence('education_grade_id', 'create')
            ->add('education_grade_id', 'ruleCheckProgrammeEndDate', [
                'rule' => ['checkProgrammeEndDate', 'education_grade_id'],
                'on' => 'create'
            ])
            ->requirePresence('academic_period_id', 'create')
            ->allowEmpty('postal_code')
            ->add('postal_code', 'ruleCustomPostalCode', [
                'rule' => ['validateCustomPattern', 'postal_code'],
                'provider' => 'table',
                'last' => true
            ])
            ->allowEmpty('old_surname', function ($context) {
                return ($context['data']['change_surname'] == 1) ? false : true;
            });
        return $validator;
    }

    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('responsible_gender_social_coordinator', ['visible' => false]);
        $this->field('teacher_education', ['visible' => false]);
        $this->field('teacher_special_education', ['visible' => false]);
        $this->field('certificate_inclusive_education', ['visible' => false]);
        $this->field('language_id', [
            'type' => 'select',
            'attr' => ['label' => __('Native Language')],
            'visible' => ['index' => false, 'view' => true, 'edit' => true]
        ]);
        $this->field('form_of_payment_id', [
            'type' => 'select',
            'visible' => ['index' => false, 'view' => true, 'edit' => true]
        ]);

        $this->field('change_surname', ['options' => $this->getSelectOptions('general.yesno') ,'visible' => ['edit' => true]]);
        $this->field('old_surname', ['visible' => false]);

        $this->field('nationality_user_id', ['type' => 'select']);
        $this->field('username', ['visible' => false]);
        $toolbarButtons = $extra['toolbarButtons'];

        // Back button does not contain the pass
        if ($this->action == 'edit' && !empty($this->paramsPass(0))) {
            $toolbarButtons['back']['url'][1] = $this->paramsPass(0)    ;
        }

        // this value comes from the list page from StudentsTable->onUpdateActionButtons
        $institutionStudentId = $this->getQueryString('institution_student_id');

        $institutionId = !empty($this->getQueryString('institution_id')) ? $this->getQueryString('institution_id') : $this->request->session()->read('Institution.Institutions.id');
        $extra['institutionId'] = $institutionId;

        // this is required if the student link is clicked from the Institution Classes or Subjects
        if (empty($institutionStudentId)) {
            $params = [];
            if ($this->paramsPass(0)) {
                $params = $this->paramsDecode($this->paramsPass(0));
            }

            $studentId = isset($params['id']) ? $params['id'] : $this->Session->read('Institution.StudentUser.primaryKey.id');

            // get the id of the latest student record in the current institution
            $InstitutionStudentsTable = TableRegistry::get('Institution.Students');
            $institutionStudentId = $InstitutionStudentsTable->find()
                ->where([
                    $InstitutionStudentsTable->aliasField('student_id') => $studentId,
                    $InstitutionStudentsTable->aliasField('institution_id') => $institutionId,
                ])
                ->order([$InstitutionStudentsTable->aliasField('created') => 'DESC'])
                ->extract('id')
                ->first();
        }
        $this->Session->write('Institution.Students.id', $institutionStudentId);
        if (empty($institutionStudentId)) { // if value is empty, redirect back to the list page
            $event->stopPropagation();
            return $this->controller->redirect(['action' => 'Students', 'index']);
        } else {
            $this->request->query['id'] = $institutionStudentId;
            $extra['institutionStudentId'] = $institutionStudentId;
        }
        $this->field('form_of_study_id', ['type' => 'select']);
        $this->field('is_acp', ['visible' => false]);
    }

    public function afterAction(Event $event, ArrayObject $extra)
    {
        $entity = $extra['entity'];
        if (!is_null($entity)) {
            $StudentTable = TableRegistry::get('Institution.Students');
            $studentEntity = $StudentTable->get($extra['institutionStudentId']);

            $userId = $this->Auth->user('id');
            $studentId = $studentEntity->student_id;

            $isStudentEnrolled = $StudentTable->checkEnrolledInInstitution($studentId, $studentEntity->institution_id); // PHPOE-1897
            $isAllowedByClass = $this->checkClassPermission($studentId, $userId); // POCOR-3010
            if (isset($extra['toolbarButtons']['edit']['url'])) {
                $extra['toolbarButtons']['edit']['url'][1] = $this->paramsEncode(['id' => $studentId]);
            }
            if (!$isStudentEnrolled || !$isAllowedByClass) {
                $this->toggle('edit', false);
            }
        }
    }

    public function viewEditBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {
        $query->contain([
            'MainNationalities', 'MainIdentityTypes', 'Genders'
        ]);
    }

    public function afterSave(Event $event, Entity $entity, ArrayObject $options){
        if ($entity->isNew()) {
            $pin = $entity->pin;
            if ($pin) {
//                $this->createRecordDisability($entity->id, $pin);
//                $this->createRecordAllowance($entity->id, $pin);
            }
        }
        else {
            $InstitutionClassStudents = TableRegistry::get('Institution.InstitutionClassStudents');
            $class = $InstitutionClassStudents->find()
                ->contain('Users')
                ->matching('StudentStatuses', function ($q) {
                    return $q->where(['StudentStatuses.code NOT IN' => ['TRANSFERRED', 'WITHDRAWN']]);
                })
                ->where([$InstitutionClassStudents->aliasField('student_id') => $entity->id])
                ->last();
            if ($class) {
                $id = $class->institution_class_id;
                $countMale = $InstitutionClassStudents->getMaleCountByClass($id);
                $countFemale = $InstitutionClassStudents->getFemaleCountByClass($id);
                $InstitutionClassStudents->InstitutionClasses->updateAll(['total_male_students' => $countMale, 'total_female_students' => $countFemale], ['id' => $id]);
            }
        }
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($entity['change_surname'] == 1) $this->field('old_surname', ['visible' => true]);
        $this->field('sop',['after'=>'identity_number']);
        $this->field('orphan',['after'=>'sop']);
        $this->field('form_of_payment_id',['after'=>'orphan']);
        $this->field('foreigner');
        $this->field('home_education');
        $this->fields['third_name']['visible'] = false;
        $this->fields['preferred_name']['visible'] = false;
        $this->fields['pin']['order'] = 3;
        if (!$this->AccessControl->isAdmin()) {
            $institutionIds = $this->AccessControl->getInstitutionsByUser();
            $this->Session->write('AccessControl.Institutions.ids', $institutionIds);
        }
        $this->Session->write('Student.Students.id', $entity->id);
        $this->Session->write('Student.Students.name', $entity->name);
        $this->setupTabElements($entity);
        $this->setupToolbarButtons($entity, $extra);

        $this->setFieldOrder([
            'openemis_no', 'last_name', 'old_surname', 'first_name', 'middle_name', 'pin', 'gender_id', 'date_of_birth'
        ]);
    }

    public function editAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($entity['change_surname'] == 1) $this->field('old_surname', ['visible' => true]);
        $this->Session->write('Student.Students.id', $entity->id);
        $this->Session->write('Student.Students.name', $entity->name);
        $this->setupTabElements($entity);

        // POCOR-3010
        $userId = $this->Auth->user('id');
        if (!$this->checkClassPermission($entity->id, $userId)) {
            $this->Alert->error('security.noAccess');
            $event->stopPropagation();
            $url = $this->url('view');
            return $this->controller->redirect($url);
        }
        // End POCOR-3010

        $this->fields['identity_number']['type'] = 'readonly'; //cant edit identity_number field value as its value is auto updated.
        $this->field('sop',['after'=>'identity_number']);
        $this->field('orphan',['after'=>'sop']);
        $this->field('form_of_payment_id',['after'=>'orphan']);

        $this->fields['nationality_id']['type'] = 'readonly';
        $this->fields['nationality_id']['attr']['value'] = $entity->has('main_nationality') ? $entity->main_nationality->name : '';

        $this->fields['identity_type_id']['type'] = 'readonly';
        $this->fields['identity_type_id']['attr']['value'] = $entity->has('main_identity_type') ? $entity->main_identity_type->name : '';

        $this->field('institution_id', ['type' => 'hidden']);
        $this->fields['institution_id']['value'] = $extra['institutionId'];
        $this->fields['third_name']['visible'] = false;
        $this->fields['preferred_name']['visible'] = false;
        $this->fields['pin']['order'] = 3;
        
        $this->field('foreigner');
        $this->field('home_education');

        $this->setFieldOrder([
            'openemis_no', 'last_name', 'old_surname','change_surname', 'first_name', 'middle_name', 'pin', 'gender_id', 'date_of_birth'
        ]);
    }

    public function onUpdateFieldSop(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldOrphan(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldForeigner(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldChangeSurname(Event $event, array $attr, $action, Request $request)
    {
        if ($request->is(['post', 'put'])) {
            if (array_key_exists($this->alias(), $request->data)) {
                if (array_key_exists('change_surname', $request->data[$this->alias()])) {
                    $result=$this->get($this->paramsDecode($request->pass[1])['id']);
                    if ($request->data[$this->alias()]['change_surname'] == 1)
                    {
                        $this->field('old_surname', ['attr'=>[ 'value' => $result->last_name],'visible'=>true ]);
                        $this->field('last_name', ['attr'=>[ 'value' => ''], 'order'=>2 ]);
                    }
                    else
                    {
                        $this->field('old_surname', ['attr'=>[ 'value' => ''], 'visible'=>false ]);
                        $this->field('last_name', ['attr'=>[ 'value' => $result->last_name] ]);
                    }

                }
            }
        }
        $attr['onChangeReload'] = 'changeIndefinite';
        return $attr;
    }

    public function onUpdateFieldHomeEducation(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    private function setupToolbarButtons(Entity $entity, ArrayObject $extra)
    {
        $toolbarButtons = $extra['toolbarButtons'];
        $toolbarButtons['back']['url']['action'] = 'Students';

        // Export execute permission.
        if (!$this->AccessControl->check(['Institutions', 'StudentUser', 'excel'])) {
            if (isset($toolbarButtons['export'])) {
                unset($toolbarButtons['export']);
            }
        }

        $this->addPromoteButton($entity, $extra);
        $this->addTransferButton($entity, $extra);
        $this->addWithdrawButton($entity, $extra);
    }

    private function setupTabElements($entity)
    {
        $id = !is_null($this->getQueryString('institution_student_id')) ? $this->getQueryString('institution_student_id') : 0;

        $options = [
            'userRole' => 'Student',
            'action' => $this->action,
            'id' => $id,
            'userId' => $entity->id
        ];

        $tabElements = $this->controller->getUserTabElements($options);
        $this->controller->set('tabElements', $tabElements);
        $this->controller->set('selectedAction', $this->alias());
    }

    private function addTransferButton(Entity $entity, ArrayObject $extra)
    {
        if ($this->AccessControl->check([$this->controller->name, 'StudentTransferOut', 'add'])) {
            $toolbarButtons = $extra['toolbarButtons'];

            $StudentsTable = TableRegistry::get('Institution.Students');
            $StudentTransfers = TableRegistry::get('Institution.InstitutionStudentTransfers');

            $institutionStudentId = $extra['institutionStudentId'];
            $studentEntity = $StudentsTable->get($institutionStudentId);

            $institutionId = $studentEntity->institution_id;
            $studentId = $studentEntity->student_id;

            $params = ['student_id' => $institutionStudentId, 'user_id' => $entity->id];
            $action = $this->setQueryString(['controller' => $this->controller->name, 'action' => 'StudentTransferOut', 'add'], $params);

            $checkIfCanTransfer = $StudentsTable->checkIfCanTransfer($studentEntity, $institutionId);

            if ($checkIfCanTransfer && !Configure::read('schoolMode')) {
                $transferButton = $toolbarButtons['back'];
                $transferButton['type'] = 'button';
                $transferButton['label'] = '<svg width="40" height="20" viewBox="0 0 40 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#293845"/> <path d="M39.167 10L30.417 17.2169L30.417 2.78314L39.167 10Z" fill="#009966"/> <path d="M21.667 17.5C21.7523 9.94392 23.447 7.3513 30.417 6.66667L30.417 13.3333C26.0671 12.8465 24.2594 14.0035 21.667 17.5Z" fill="#009966"/> </svg> ';
                $transferButton['attr']['class'] = 'btn btn-xs btn-default icon-big';
                $transferButton['attr']['title'] = __('Transfer');
                $transferButton['url'] = $action;
                $toolbarButtons['transfer'] = $transferButton;
            }
        }
    }

    private function addPromoteButton(Entity $entity, ArrayObject $extra)
    {
        if ($this->AccessControl->check([$this->controller->name, 'Promotion', 'add'])) {
            $toolbarButtons = $extra['toolbarButtons'];

            $StudentsTable = TableRegistry::get('Institution.Students');
            $StudentStatuses = TableRegistry::get('Student.StudentStatuses');
            $AcademicPeriods = TableRegistry::get('AcademicPeriod.AcademicPeriods');
            $editableAcademicPeriods = $AcademicPeriods->getYearList(['isEditable' => true]);

            $Enrolled = $StudentStatuses->getIdByCode('CURRENT');
            $institutionStudentId = $extra['institutionStudentId'];
            $studentEntity = $StudentsTable->get($institutionStudentId);
            $academicPeriodId = $studentEntity->academic_period_id;

            $params = ['student_id' => $institutionStudentId, 'user_id' => $entity->id];
            $action = $this->setUrlParams(['controller' => $this->controller->name, 'action' => 'IndividualPromotion', 'add'], $params);

            // Show Promote button only if the Student Status is Current and academic period is editable
            if ($studentEntity->student_status_id == $Enrolled && array_key_exists($academicPeriodId, $editableAcademicPeriods)) {
                // Promote button
                $promoteButton = $toolbarButtons['back'];
                $promoteButton['type'] = 'button';
                $promoteButton['label'] = '<svg width="35" height="20" viewBox="0 0 35 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#293845"/> <path d="M27.4995 10L25.8328 19.1667L22.4995 19.1667L20.8328 10L27.4995 10Z" fill="#009966"/> <path d="M24.1663 1.66669L31.3832 10.4167H16.9495L24.1663 1.66669Z" fill="#009966"/> </svg>';
                $promoteButton['attr']['class'] = 'btn btn-xs btn-default icon-big';
                $promoteButton['attr']['title'] = __('Promotion / Repeat');
                $promoteButton['url'] = $action;

                $toolbarButtons['promote'] = $promoteButton;
                //End
            }
        }
    }

    private function addWithdrawButton(Entity $entity, ArrayObject $extra)
    {
        if ($this->AccessControl->check([$this->controller->name, 'WithdrawRequests', 'add'])) {
            $session = $this->Session;
            $toolbarButtons = $extra['toolbarButtons'];

            $InstitutionStudentsTable = TableRegistry::get('Institution.Students');
            $StudentsTable = TableRegistry::get('Institution.Students');
            $StudentStatuses = TableRegistry::get('Student.StudentStatuses');

            $institutionStudentId = $extra['institutionStudentId'];
            $studentEntity = $StudentsTable->get($institutionStudentId);
            $enrolledStatus = $StudentStatuses->getIdByCode('CURRENT');

            // Check if the student is enrolled
            if ($studentEntity->student_status_id == $enrolledStatus) {
                $StudentStatusUpdates = TableRegistry::get('Institution.StudentStatusUpdates');
                $WithdrawRequests = TableRegistry::get('Institution.WithdrawRequests');
                $session->write($WithdrawRequests->registryAlias().'.id', $institutionStudentId);
                $WorkflowModels = TableRegistry::get('Workflow.WorkflowModels');
                $approvedStatus = $WorkflowModels->getWorkflowStatusSteps('Institution.StudentWithdraw', 'APPROVED');

                $rejectedStatus = $WorkflowModels->getWorkflowStatusSteps('Institution.StudentWithdraw', 'REJECTED');
                $status = $rejectedStatus + $approvedStatus;

                try {
                    // check if there is an existing withdraw request
                    $withdrawRequest = $WithdrawRequests->find()
                        ->select(['institution_student_withdraw_id' => 'id'])
                        ->where([
                            $WithdrawRequests->aliasField('student_id') => $studentEntity->student_id,
                            $WithdrawRequests->aliasField('institution_id') => $studentEntity->institution_id,
                            $WithdrawRequests->aliasField('education_grade_id') => $studentEntity->education_grade_id,
                            $WithdrawRequests->aliasField('status_id').' NOT IN' => $status
                        ])
                        ->first();
                    $studentStatusUpdates = $StudentStatusUpdates->find()
                        ->where([
                            $StudentStatusUpdates->aliasField('security_user_id') => $studentEntity->student_id,
                            $StudentStatusUpdates->aliasField('institution_id') => $studentEntity->institution_id,
                            $StudentStatusUpdates->aliasField('education_grade_id') => $studentEntity->education_grade_id,
                            $StudentStatusUpdates->aliasField('academic_period_id') => $studentEntity->academic_period_id,
                            $StudentStatusUpdates->aliasField('execution_status') => 1
                        ])
                        ->first();

                } catch (DatabaseException $e) {
                    $withdrawRequest = false;
                    $this->Alert->error('WithdrawRequests.configureWorkflowStatus');
                }

                $withdrawButton = $toolbarButtons['back'];
                $withdrawButton['type'] = 'button';
                $withdrawButton['label'] = '<svg width="26" height="20" viewBox="0 0 26 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M16.3882 12.4117C15.3426 11.3662 14.0981 10.5921 12.7402 10.1236C14.1945 9.12191 15.15 7.44555 15.15 5.55C15.15 2.48974 12.6603 0 9.6 0C6.53974 0 4.05 2.48974 4.05 5.55C4.05 7.44555 5.00546 9.12191 6.45982 10.1236C5.10195 10.5921 3.8574 11.3662 2.81179 12.4117C0.998588 14.225 0 16.6357 0 19.2H1.5C1.5 14.7336 5.13364 11.1 9.6 11.1C14.0664 11.1 17.7 14.7336 17.7 19.2H19.2C19.2 16.6357 18.2014 14.225 16.3882 12.4117ZM9.6 9.6C7.36684 9.6 5.55 7.7832 5.55 5.55C5.55 3.3168 7.36684 1.5 9.6 1.5C11.8332 1.5 13.65 3.3168 13.65 5.55C13.65 7.7832 11.8332 9.6 9.6 9.6Z" fill="#293845"/> <path d="M12.7998 13.5C12.7998 9.91015 15.71 7 19.2998 7V7C22.8897 7 25.7998 9.91015 25.7998 13.5V13.5C25.7998 17.0899 22.8897 20 19.2998 20V20C15.71 20 12.7998 17.0899 12.7998 13.5V13.5Z" fill="white"/> <path fill-rule="evenodd" clip-rule="evenodd" d="M19.2996 19.4584C16.0089 19.4584 13.3413 16.7907 13.3413 13.5C13.3413 10.2093 16.0089 7.54169 19.2996 7.54169C22.5903 7.54169 25.258 10.2093 25.258 13.5C25.258 16.7907 22.5903 19.4584 19.2996 19.4584ZM19.2996 18.375C21.992 18.375 24.1746 16.1924 24.1746 13.5C24.1746 10.8076 21.992 8.62501 19.2996 8.62501C16.6072 8.62501 14.4246 10.8076 14.4246 13.5C14.4246 16.1924 16.6072 18.375 19.2996 18.375ZM17.516 16.0497L19.2996 14.2661L21.0833 16.0497L21.8493 15.2837L20.0657 13.5L21.8493 11.7164L21.0833 10.9503L19.2996 12.734L17.516 10.9503L16.7499 11.7164L18.5336 13.5L16.7499 15.2837L17.516 16.0497Z" fill="#C71100"/> </svg>';
                $withdrawButton['attr']['class'] = 'btn btn-xs btn-default icon-big';
                $withdrawButton['attr']['title'] = __('Withdraw');

                $withdrawButton['url'] = $this->url('add', 'QUERY');
                if (!empty($withdrawRequest)) {
                    $withdrawButton['url']['action'] = 'StudentWithdraw';
                    $withdrawButton['url'][0] = 'view';
                    $withdrawButton['url'][1] = $this->paramsEncode(['id' => $withdrawRequest->institution_student_withdraw_id]);
                    $toolbarButtons['withdraw'] = $withdrawButton;
                } elseif (!empty($studentStatusUpdates)) {
                    $withdrawButton['url']['action'] = 'StudentStatusUpdates';
                    $withdrawButton['url'][0] = 'view';
                    $withdrawButton['url'][1] = $this->paramsEncode(['id' => $studentStatusUpdates->id]);
                    $toolbarButtons['withdraw'] = $withdrawButton;
                } else {
                    $withdrawButton['url']['action'] = 'WithdrawRequests';
                    $toolbarButtons['withdraw'] = $withdrawButton;
                }
            }
        }
    }

    //to handle identity_number field that is automatically created by mandatory behaviour.
    public function onUpdateFieldIdentityNumber(Event $event, array $attr, $action, Request $request)
    {
        if ($action == 'add') {
            $attr['fieldName'] = $this->alias().'.identities.0.number';
            $attr['attr']['label'] = __('Identity Number');
        }
        return $attr;
    }

    public function studentsAfterSave(Event $event, $student)
    {
        if ($student->isNew()) {
            $this->updateAll(['is_student' => 1], ['id' => $student->student_id]);
        }
    }

    public function pullBeforePatch(Event $event, Entity $entity, ArrayObject $queryString, ArrayObject $patchOption, ArrayObject $extra)
    {
        if (!array_key_exists('institution_id', $queryString)) {
            $session = $this->request->session();
            $queryString['institution_id'] = !empty($this->request->param('institutionId')) ? $this->paramsDecode($this->request->param('institutionId'))['id'] : $session->read('Institution.Institutions.id');
        }
    }

    private function checkClassPermission($studentId, $userId)
    {
        $permission = false;
        if (!$this->AccessControl->isAdmin()) {
            $event = $this->controller->dispatchEvent('Controller.SecurityAuthorize.onUpdateRoles', null, $this);
            $roles = [];
            if (is_array($event->result)) {
                $roles = $event->result;
            }
            if (!$this->AccessControl->check(['Institutions', 'AllClasses', $permission], $roles)) {
                $Class = TableRegistry::get('Institution.InstitutionClasses');
                $classStudentRecord = $Class
                    ->find('ByAccess', [
                        'accessControl' => $this->AccessControl,
                        'controller' => $this->controller,
                        'userId' => $userId,
                        'permission' => 'edit'
                    ])
                    ->innerJoinWith('ClassStudents')
                    ->where(['ClassStudents.student_id' => $studentId])
                    ->toArray();
                if (!empty($classStudentRecord)) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        } else {
            $permission = true;
        }
        return $permission;
    }

    public function onExcelUpdateFields(Event $event, ArrayObject $settings, ArrayObject $fields)
    {
        $IdentityType = TableRegistry::get('FieldOption.IdentityTypes');
        $identity = $IdentityType->getDefaultEntity();

        foreach ($fields as $key => $field) {
            //get the value from the table, but change the label to become default identity type.
            if ($field['field'] == 'identity_number') {
                $fields[$key] = [
                    'key' => 'StudentUser.identity_number',
                    'field' => 'identity_number',
                    'type' => 'string',
                    'label' => __($identity->name)
                ];
                break;
            }
        }
    }

    public function getAcademicTabElements($options = [])
    {
        $id = (array_key_exists('id', $options))? $options['id']: 0;

        $tabElements = [];
        $studentTabElements = [
            'Programmes' => ['text' => __('Programmes')],
            'Classes' => ['text' => __('Classes')],
            'Subjects' => ['text' => __('Subjects')],
            'StudentPractice' => ['text' => __('Practice')],
            'StudentEducation' => ['text' => __('Student Education')],
            'Absences' => ['text' => __('Absences')],
            'Behaviours' => ['text' => __('Behaviours')],
            'Outcomes' => ['text' => __('Outcomes')],
            'Competencies' => ['text' => __('Competencies')],
            'Results' => ['text' => __('Assessments')],
            'ExaminationResults' => ['text' => __('Examinations')],
            'ReportCards' => ['text' => __('Report Cards')],
            'Awards' => ['text' => __('Awards')],
            'Extracurriculars' => ['text' => __('Extracurriculars')],
            'Textbooks' => ['text' => __('Textbooks')],
            'Risks' => ['text' => __('Risks')]
        ];

        $tabElements = array_merge($tabElements, $studentTabElements);

        // Programme & Textbooks will use institution controller, other will be still using student controller
        foreach ($studentTabElements as $key => $tab) {
            if ($key == 'Programmes' || $key == 'Textbooks') {
                $type = (array_key_exists('type', $options))? $options['type']: null;
                $studentUrl = ['plugin' => 'Institution', 'controller' => 'Institutions'];
                $tabElements[$key]['url'] = array_merge($studentUrl, ['action' =>'Student'.$key, 'index', 'type' => $type]);
            } elseif ($key == 'Risks') {
                $type = (array_key_exists('type', $options))? $options['type']: null;
                $studentUrl = ['plugin' => 'Institution', 'controller' => 'Institutions'];
                $tabElements[$key]['url'] = array_merge($studentUrl, ['action' =>'Student'.$key, 'index', 'type' => $type]);
            } else {
                $studentUrl = ['plugin' => 'Student', 'controller' => 'Students'];
                $tabElements[$key]['url'] = array_merge($studentUrl, ['action' =>$key, 'index']);
            }
        }

        if (Configure::read('schoolMode')) {
            if (isset($tabElements['ExaminationResults'])) {
                unset($tabElements['ExaminationResults']);
            }

            if (!in_array('Risks', (array)Configure::read('School.excludedPlugins'))) {
                if (isset($tabElements['Risks'])) {
                    unset($tabElements['Risks']);
                }
            }
        }

        return $tabElements;
    }

    // needs to migrate
    public function findStudents(Query $query, array $options = [])
    {
        $query->where([$this->aliasField('super_admin').' <> ' => 1]);

        $limit = (array_key_exists('limit', $options))? $options['limit']: null;
        $page = (array_key_exists('page', $options))? $options['page']: null;

        // conditions
        $firstName = (array_key_exists('first_name', $options))? $options['first_name']: null;
        $lastName = (array_key_exists('last_name', $options))? $options['last_name']: null;
        $openemisNo = (array_key_exists('openemis_no', $options))? $options['openemis_no']: null;
        $pin = (array_key_exists('pin', $options)) ? $options['pin']: null;
        $identityNumber = (array_key_exists('identity_number', $options))? $options['identity_number']: null;
        $dateOfBirth = (array_key_exists('date_of_birth', $options))? $options['date_of_birth']: null;

        if (is_null($firstName) && is_null($lastName) && is_null($openemisNo) && is_null($identityNumber) && is_null($dateOfBirth) && is_null($pin)) {
            return $query->where(['1 = 0']);
        }

        $conditions = [];
        if (!empty($firstName)) {
            $conditions['first_name LIKE'] = $firstName . '%';
        }
        if (!empty($lastName)) {
            $conditions['last_name LIKE'] = $lastName . '%';
        }
        if (!empty($openemisNo)) {
            $conditions['openemis_no LIKE'] = $openemisNo . '%';
        }
        if (!empty($pin)) {
            $conditions['pin'] = $pin;
        }
        if (!empty($dateOfBirth)) {
            $conditions['date_of_birth'] = date_create($dateOfBirth)->format('Y-m-d');
        }

        $identityConditions = [];
        if (!empty($identityNumber)) {
            $identityConditions['Identities.number LIKE'] = $identityNumber . '%';
        }

        $identityJoinType = (empty($identityNumber))? 'LEFT': 'INNER';
        $query->join([
            [
                'type' => $identityJoinType,
                'table' => 'user_identities',
                'alias' => 'Identities',
                'conditions' => array_merge([
                    'Identities.security_user_id = ' . $this->aliasField('id')
                ], $identityConditions)
            ]
        ]);

        $query->group([$this->aliasField('id')]);

        if (!empty($conditions)) {
            $query->where($conditions);
        }
        if (!is_null($limit)) {
            $query->limit($limit);
        }
        if (!is_null($page)) {
            $query->page($page);
        }

        return $query;
    }

    // needs to migrate
    public function findEnrolledInstitutionStudents(Query $query, array $options = [])
    {
        $query->contain([
            'InstitutionStudents' => function ($q) {
                return $q->where(['InstitutionStudents.student_status_id' => 1]);
            },
            'InstitutionStudents.Institutions.Areas',
            'InstitutionStudents.AcademicPeriods',
            'InstitutionStudents.EducationGrades'
        ]);
        return $query;
    }
}
