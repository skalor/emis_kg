<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Network\Request;
use Cake\Utility\Text;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use App\Model\Table\AppTable;
use Cake\Network\Session;
use App\Model\Table\ControllerActionTable;
use App\Model\Traits\OptionsTrait;

class StaffUserTable extends ControllerActionTable
{
    use OptionsTrait;
    public function initialize(array $config)
    {
        $this->table('security_users');
        $this->entityClass('User.User');
        parent::initialize($config);
        self::handleAssociations($this);
        // Behaviors
        $this->addBehavior('User.User');
        $this->addBehavior('User.AdvancedNameSearch');
        $this->addBehavior('User.Mandatory', ['userRole' => 'Staff', 'roleFields' =>['Identities', 'Nationalities', 'Contacts']]);
        $this->addBehavior('AdvanceSearch');

        $this->addBehavior('CustomField.Record', [
            'model' => 'Staff.Staff',
            'behavior' => 'Staff',
            'fieldKey' => 'staff_custom_field_id',
            'tableColumnKey' => 'staff_custom_table_column_id',
            'tableRowKey' => 'staff_custom_table_row_id',
            'fieldClass' => ['className' => 'StaffCustomField.StaffCustomFields'],
            'formKey' => 'staff_custom_form_id',
            'filterKey' => 'staff_custom_filter_id',
            'formFieldClass' => ['className' => 'StaffCustomField.StaffCustomFormsFields'],
            'formFilterClass' => ['className' => 'StaffCustomField.StaffCustomFormsFilters'],
            'recordKey' => 'staff_id',
            'fieldValueClass' => ['className' => 'StaffCustomField.StaffCustomFieldValues', 'foreignKey' => 'staff_id', 'dependent' => true, 'cascadeCallbacks' => true],
            'tableCellClass' => ['className' => 'StaffCustomField.StaffCustomTableCells', 'foreignKey' => 'staff_id', 'dependent' => true, 'cascadeCallbacks' => true, 'saveStrategy' => 'replace']
        ]);

        $this->addBehavior('Excel', [
            'excludes' => ['photo_name', 'is_student', 'is_staff', 'is_guardian', 'super_admin', 'date_of_death' ],
            'filename' => 'Staff',
            'pages' => ['view']
        ]);

        $this->addBehavior('Institution.UserReferenceGenerate', ['type' => 'staff']);

        $this->addBehavior('HighChart', [
            'count_by_gender' => [
                '_function' => 'getNumberOfStaffByGender'
            ]
        ]);
        $this->belongsTo('Languages', ['className' => 'Languages', 'foreignKey' => 'language_id']);

        $this->addBehavior('Configuration.Pull');
        $this->addBehavior('TrackActivity', ['target' => 'User.UserActivities', 'key' => 'security_user_id', 'session' => 'Staff.Staff.id']);
        $this->addBehavior('Restful.RestfulAccessControl', [
            'Staff' => ['index', 'add', 'edit'],
            'ReportCardComments' => ['view']
        ]);
        $this->toggle('index', false);
        $this->toggle('add', false);
        $this->toggle('remove', false);
    }

    public static function handleAssociations($model)
    {
        $model->belongsTo('Genders', ['className' => 'User.Genders']);
        $model->belongsTo('AddressAreas', ['className' => 'Area.AreaAdministratives', 'foreignKey' => 'address_area_id']);
        $model->belongsTo('BirthplaceAreas', ['className' => 'Area.AreaAdministratives', 'foreignKey' => 'birthplace_area_id']);
        $model->belongsTo('MainNationalities', ['className' => 'FieldOption.Nationalities', 'foreignKey' => 'nationality_id']);
        $model->belongsTo('MainIdentityTypes', ['className' => 'FieldOption.IdentityTypes', 'foreignKey' => 'identity_type_id']);
        $model->belongsTo('NationalitiesUsers', ['className' => 'FieldOption.NationalitiesUsers', 'foreignKey' => 'nationality_user_id']);
        $model->belongsTo('FormOfStudy', ['className' => 'FieldOption.NationalitiesUsers', 'foreignKey' => 'form_of_study_id']);

        $model->hasMany('Identities', ['className' => 'User.Identities',      'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Nationalities', ['className' => 'User.UserNationalities',   'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Contacts', ['className' => 'User.Contacts',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Attachments', ['className' => 'User.Attachments',     'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('BankAccounts', ['className' => 'User.BankAccounts',    'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Comments', ['className' => 'User.Comments',        'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Languages', ['className' => 'User.UserLanguages',   'foreignKey' => 'security_user_id', 'dependent' => true]);
        $model->hasMany('Awards', ['className' => 'User.Awards',          'foreignKey' => 'security_user_id', 'dependent' => true]);
        //$model->hasMany('Qualifications', ['className' => 'FieldOption.Qualifications','foreignKey' => 'staff_id']);

        $model->hasMany('SpecialNeeds', ['className' => 'SpecialNeeds.SpecialNeedsAssessments',    'foreignKey' => 'security_user_id', 'dependent' => true]);

        $model->belongsToMany('SecurityRoles', [
            'className' => 'Security.SecurityRoles',
            'foreignKey' => 'security_role_id',
            'targetForeignKey' => 'security_user_id',
            'through' => 'Security.SecurityGroupUsers',
            'dependent' => true
        ]);

        $model->belongsToMany('Institutions', [
            'className' => 'Institution.Institutions',
            'joinTable' => 'institution_staff', // will need to change to institution_staff
            'foreignKey' => 'staff_id', // will need to change to staff_id
            'targetForeignKey' => 'institution_id', // will need to change to institution_id
            'through' => 'Institution.Staff',
            'dependent' => true
        ]);

        // class should never cascade delete
        $model->hasMany('InstitutionClasses', ['className' => 'Institution.InstitutionClasses', 'foreignKey' => 'staff_id']);
        $model->hasMany('InstitutionStudents', ['className' => 'Institution.Students',    'foreignKey' => 'student_id', 'dependent' => true]);
        $model->hasMany('InstitutionStaff', ['className' => 'Institution.Staff',    'foreignKey' => 'staff_id', 'dependent' => true]);

        $model->belongsToMany('Subjects', [
            'className' => 'Institution.InstitutionSubject',
            'joinTable' => 'institution_subject_staff',
            'foreignKey' => 'staff_id',
            'targetForeignKey' => 'institution_subject_id',
            'through' => 'Institution.InstitutionSubjectStaff',
            'dependent' => true
        ]);

        $model->hasMany('StaffActivities', ['className' => 'Staff.StaffActivities', 'foreignKey' => 'staff_id', 'dependent' => true]);
        $model->hasMany('InstitutionRubrics', ['className' => 'Institution.InstitutionRubrics', 'foreignKey' => 'staff_id', 'dependent' => true]);
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Model.Staff.afterSave'] = 'staffAfterSave';
        return $events;
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

    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('nationality_user_id', ['type' => 'select']);
        $this->field('username', ['visible' => false]);
        $toolbarButtons = $extra['toolbarButtons'];
        if ($this->action == 'view') {
            $id = $this->request->query('id');
            $this->Session->write('Institution.Staff.id', $id);
            if ($toolbarButtons->offsetExists('back')) {
                $toolbarButtons['back']['url']['action'] = 'Staff';
            }
        } else {
            if ($toolbarButtons->offsetExists('back')) {
                $toolbarButtons['back']['url'][1] = $this->paramsPass(0);
            }
        }

        $this->field('change_surname', ['options' => $this->getSelectOptions('general.yesno') ,'visible' => ['edit' => true]]);
        $this->field('old_surname', ['visible' => false]);

        $this->field('language_id', [
            'type' => 'select',
            'attr' => ['label' => __('Native Language')],
            'visible' => ['index' => false, 'view' => true, 'edit' => true]
        ]);
        $this->field('sop', ['visible' => false]);
        $this->field('orphan', ['visible' => false]);
        $this->field('form_of_payment_id', ['visible' => false]);
        $this->field('form_of_study_id', ['visible' => false]);
        $this->field('foreigner', ['visible' => false]);
        $this->field('home_education', ['visible' => false]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $BaseUsers = TableRegistry::get('User.Users');
        $validator = $BaseUsers->setUserValidation($validator, $this);
        $validator
            ->allowEmpty('username')
            ->allowEmpty('postal_code')
            ->add('postal_code', 'ruleCustomPostalCode', [
                'rule' => ['validateCustomPattern', 'postal_code'],
                'provider' => 'table',
                'last' => true
            ])
            ->allowEmpty('photo_content')
            ->allowEmpty('language_id')
            ->add('staff_name', 'ruleInstitutionStaffId', [
                'rule' => ['institutionStaffId'],
                'on' => 'create'
            ])
            ->add('staff_assignment', 'ruleTransferRequestExists', [
                'rule' => ['checkPendingStaffTransfer'],
                'on' => 'create'
            ])
            ->add('staff_assignment', 'ruleCheckStaffAssignment', [
                'rule' => ['checkStaffAssignment'],
                'on' => 'create'
            ])
            ->notEmpty('fte', null, 'create')
            ->notEmpty('position_type', null, 'create')
            ->notEmpty('institution_position_id', null, 'create')
            ->notEmpty('staff_type_id', null, 'create')
            ->requirePresence('fte', 'create')
            ->requirePresence('start_date', 'create')
            ->requirePresence('position_type', 'create')
            ->requirePresence('institution_position_id', 'create')
            ->requirePresence('staff_type_id', 'create')
//            ->add('start_date', 'ruleInAcademicPeriod', [
//                'rule' => ['inAcademicPeriod', 'academic_period_id', []],
//                'on' => function ($context) {
//                    // check for staff add wizard on create operations - where academic_period_id exist in the context data - POCOR-4576
//                    return ($context['newRecord'] && array_key_exists('academic_period_id', $context['data']));
//                }
//            ])
            ->allowEmpty('old_surname', function ($context) {
                return ($context['data']['change_surname'] == 1) ? false : true;
            });
        return $validator;
    }

    public function viewEditBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {
        $query->contain([
            'MainNationalities', 'MainIdentityTypes'
        ]);
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        if ($entity['change_surname'] == 1) $this->field('old_surname', ['visible' => true]);
        $this->field('responsible_gender_social_coordinator');
        $this->field('teacher_education');
        $this->field('teacher_special_education');
        $this->field('certificate_inclusive_education');

        $this->fields['third_name']['visible'] = false;
        $this->fields['preferred_name']['visible'] = false;
        $this->fields['pin']['order'] = 3;
        if (!$this->AccessControl->isAdmin()) {
            $institutionIds = $this->AccessControl->getInstitutionsByUser();
            $this->Session->write('AccessControl.Institutions.ids', $institutionIds);
        }
        $this->Session->write('Staff.Staff.id', $entity->id);
        $this->Session->write('Staff.Staff.name', $entity->name);
        $this->setupTabElements($entity);

        $this->addTransferButton($entity, $extra);
        $this->addReleaseButton($entity, $extra);
        $this->field('is_acp');

        $this->setFieldOrder([
            'openemis_no', 'last_name', 'old_surname', 'first_name', 'middle_name', 'pin', 'gender_id', 'date_of_birth', 'nationality_id', 'is_acp', 'institution_pin'
        ]);
    }

    private function addReleaseButton(Entity $entity, ArrayObject $extra)
    {
        if($this->AccessControl->check([$this->controller->name, 'StaffRelease', 'add'])) {

            $session = $this->request->session();
            $toolbarButtons = $extra['toolbarButtons'];
            $StaffTable = TableRegistry::get('Institution.Staff');
            $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
            $ConfigStaffReleaseTable = TableRegistry::get('Configuration.ConfigStaffReleases');

            $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
            $institutionId = isset($this->request->params['institutionId']) ? $this->paramsDecode($this->request->params['institutionId'])['id'] : $session->read('Institution.Institutions.id');
            $userId = $entity->id;

            $enableStaffRelease = $ConfigStaffReleaseTable->checkIfReleaseEnabled($institutionId);

            $assignedStaffRecords = $StaffTable->find()
                ->where([
                    $StaffTable->aliasField('staff_id') => $userId,
                    $StaffTable->aliasField('institution_id') => $institutionId,
                    $StaffTable->aliasField('staff_status_id') => $assignedStatus
                ])
                ->count();

            if ($enableStaffRelease && $assignedStaffRecords > 0) {
                $url = [
                    'plugin' => $this->controller->plugin,
                    'controller' => $this->controller->name,
                    'institutionId' => $this->paramsEncode(['id' => $institutionId]),
                    'action' => 'StaffRelease',
                    'add'
                ];

                $releaseButton = $toolbarButtons['back'];
                $releaseButton['type'] = 'button';
                $releaseButton['label'] = '<i class="fa kd-release"></i>';
                $releaseButton['attr']['class'] = 'btn btn-xs btn-default icon-big';
                $releaseButton['attr']['title'] = __('Release');
                $releaseButton['url'] = $this->setQueryString($url, ['user_id' => $userId]);

                $toolbarButtons['release'] = $releaseButton;
            }
        }
    }

    private function addTransferButton(Entity $entity, ArrayObject $extra)
    {
        if ($this->AccessControl->check([$this->controller->name, 'StaffTransferOut', 'add'])) {
            $session = $this->request->session();
            $toolbarButtons = $extra['toolbarButtons'];
            $StaffTable = TableRegistry::get('Institution.Staff');
            $StaffStatuses = TableRegistry::get('Staff.StaffStatuses');
            $ConfigStaffTransfersTable = TableRegistry::get('Configuration.ConfigStaffTransfers');

            $assignedStatus = $StaffStatuses->getIdByCode('ASSIGNED');
            $institutionId = isset($this->request->params['institutionId']) ? $this->paramsDecode($this->request->params['institutionId'])['id'] : $session->read('Institution.Institutions.id');
            $userId = $entity->id;

            $enableStaffTransfer = $ConfigStaffTransfersTable->checkIfTransferEnabled($institutionId);

            $assignedStaffRecords = $StaffTable->find()
                ->where([
                    $StaffTable->aliasField('staff_id') => $userId,
                    $StaffTable->aliasField('institution_id') => $institutionId,
                    $StaffTable->aliasField('staff_status_id') => $assignedStatus
                ])
                ->count();

            if ($enableStaffTransfer && $assignedStaffRecords > 0) {
                $url = [
                    'plugin' => $this->controller->plugin,
                    'controller' => $this->controller->name,
                    'institutionId' => $this->paramsEncode(['id' => $institutionId]),
                    'action' => 'StaffTransferOut',
                    'add'
                ];

                $transferButton = $toolbarButtons['back'];
                $transferButton['type'] = 'button';
                $transferButton['label'] = '<svg width="40" height="20" viewBox="0 0 40 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M17.0711 12.9289C15.9819 11.8398 14.6855 11.0335 13.2711 10.5454C14.786 9.50199 15.7812 7.75578 15.7812 5.78125C15.7812 2.59348 13.1878 0 10 0C6.81223 0 4.21875 2.59348 4.21875 5.78125C4.21875 7.75578 5.21402 9.50199 6.72898 10.5454C5.31453 11.0335 4.01813 11.8398 2.92895 12.9289C1.0402 14.8177 0 17.3289 0 20H1.5625C1.5625 15.3475 5.34754 11.5625 10 11.5625C14.6525 11.5625 18.4375 15.3475 18.4375 20H20C20 17.3289 18.9598 14.8177 17.0711 12.9289ZM10 10C7.67379 10 5.78125 8.1075 5.78125 5.78125C5.78125 3.455 7.67379 1.5625 10 1.5625C12.3262 1.5625 14.2188 3.455 14.2188 5.78125C14.2188 8.1075 12.3262 10 10 10Z" fill="#293845"></path> <path d="M39.1665 10L30.4165 17.2169L30.4165 2.78314L39.1665 10Z" fill="#009966"></path> <path d="M21.6665 17.5C21.7518 9.94392 23.4465 7.3513 30.4165 6.66667L30.4165 13.3333C26.0666 12.8465 24.2589 14.0035 21.6665 17.5Z" fill="#009966"></path> </svg>';
                $transferButton['attr']['class'] = 'btn btn-xs btn-default icon-big';
                $transferButton['attr']['title'] = __('Transfer');
                $transferButton['url'] = $this->setQueryString($url, ['user_id' => $userId]);

                $toolbarButtons['transfer'] = $transferButton;
            }
        }
    }

    public function editAfterAction(Event $event, Entity $entity)
    {
        if ($entity['change_surname'] == 1) $this->field('old_surname', ['visible' => true]);
        $this->field('is_acp');
        $this->field('responsible_gender_social_coordinator');
        $this->field('teacher_education');
        $this->field('teacher_special_education');
        $this->field('certificate_inclusive_education');

        $this->Session->write('Staff.Staff.id', $entity->id);
        $this->Session->write('Staff.Staff.name', $entity->name);
        $this->setupTabElements($entity);

        $this->fields['identity_number']['type'] = 'readonly'; //cant edit identity_number field value as its value is auto updated.

        $this->fields['nationality_id']['type'] = 'readonly';
        $this->fields['nationality_id']['attr']['value'] = $entity->has('main_nationality') ? $entity->main_nationality->name : '';

        $this->fields['identity_type_id']['type'] = 'readonly';
        $this->fields['identity_type_id']['attr']['value'] = $entity->has('main_identity_type') ? $entity->main_identity_type->name : '';
        $this->fields['third_name']['visible'] = false;
        $this->fields['preferred_name']['visible'] = false;
        $this->fields['pin']['order'] = 3;

        $this->fields['mobile_phone']['type'] = 'readonly';

        $this->setFieldOrder([
            'openemis_no', 'last_name', 'old_surname','change_surname','first_name', 'middle_name', 'pin', 'gender_id', 'date_of_birth', 'nationality_id', 'is_acp', 'institution_pin'
        ]);
    }

    public function onUpdateFieldIsAcp(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldResponsibleGenderSocialCoordinator(Event $event, array $attr, $action, Request $request)
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

    public function onUpdateFieldTeacherEducation(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldTeacherSpecialEducation(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }

    public function onUpdateFieldCertificateInclusiveEducation(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }


    private function setupTabElements($entity)
    {
        $id = !is_null($this->request->query('id')) ? $this->request->query('id') : 0;
        $options = [
            'userRole' => 'Staff',
            'action' => $this->action,
            'id' => $id,
            'userId' => $entity->id
        ];

        $tabElements = $this->controller->getUserTabElements($options);

        $this->controller->set('tabElements', $tabElements);
        $this->controller->set('selectedAction', $this->alias());
    }

    public function staffAfterSave(Event $event, $staff)
    {
        if ($staff->isNew()) {
            $this->updateAll(['is_staff' => 1], ['id' => $staff->staff_id]);
        }
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

    public function findStaff(Query $query, array $options = [])
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

    public function findAssignedInstitutionStaff(Query $query, array $options = [])
    {
        $institutionId = $options['institution_id'];
        $startDate = $options['start_date'];

        $query->contain([
            'InstitutionStaff' => function ($q) use ($institutionId, $startDate) {
                return $q->where([
                    'InstitutionStaff.institution_id <>' => $institutionId,
                    'InstitutionStaff.start_date < ' => $startDate,
                    'OR' => [
                        ['InstitutionStaff.end_date >= ' => $startDate],
                        ['InstitutionStaff.end_date IS NULL']
                    ]
                ])
                ->order(['InstitutionStaff.created' => 'desc']);
            },
            'InstitutionStaff.Institutions.Areas'
        ]);
        return $query;
    }
}
