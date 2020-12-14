<?php
namespace Institution\Model\Table;
use ArrayObject;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\I18n\Time;
use Cake\I18n\Date;
use DateTime;

class InstitutionFacultiesTable extends ControllerActionTable
{
    private $institutionId;
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->hasMany('InstitutionGrades', ['className' => 'Institution.InstitutionGrades']);
        $this->belongsTo('Staff', ['className' => 'User.Users', 'foreignKey' => 'head_id']);
        $this->setDeleteStrategy('restrict');
    }


    public function beforeAction(Event $event, ArrayObject $extra) {
        $this->institutionId = $this->Session->read('Institution.Institutions.id');
    }

    public function afterAction(Event $event, ArrayObject $extra){

        $action = $this->action;
        $this->field('closing_date', ['default_date' => false]);
        $this->setFieldOrder([
            'name', 'short_name','opening_date', 'closing_date'
        ]);
        if ($action == 'add' || $action == 'edit'){
            $headOptions = $this->getHeadOptions($this->institutionId);
            $this->fields['head_id']['options'] = $headOptions;
            $this->fields['head_id']['select'] = true;
        }
    }

    public function getHeadOptions($institutionId)
    {
        $todayDate = new Date();

        $Staff = $this->Institutions->Staff;
        $query = $Staff->find('all')
            ->select([
                $Staff->Users->aliasField('id'),
                $Staff->Users->aliasField('openemis_no'),
                $Staff->Users->aliasField('first_name'),
                $Staff->Users->aliasField('middle_name'),
                $Staff->Users->aliasField('third_name'),
                $Staff->Users->aliasField('last_name'),
                $Staff->Users->aliasField('preferred_name')
            ])
            ->contain(['Users'])
            ->find('byInstitution', ['Institutions.id'=>$institutionId])
            ->where([
                $Staff->aliasField('start_date <= ') => $todayDate,
                'OR' => [
                    [$Staff->aliasField('end_date >= ') => $todayDate],
                    [$Staff->aliasField('end_date IS NULL')]
                ]
            ])
            ->order([
                $Staff->Users->aliasField('first_name')
            ])
            ->formatResults(function ($results) {
                $returnArr = [];
                foreach ($results as $result) {
                    if ($result->has('Users')) {
                        $returnArr[$result->Users->id] = $result->Users->name_with_id;
                    }
                }
                return $returnArr;
            });

        $options = $query->toArray();

        return $options;
    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){
        $Institution = TableRegistry::get('Institution.Institutions');
        $institution = $Institution->find()->where([$Institution->aliasField($Institution->primaryKey()) => $this->institutionId])->first();

        if (empty($institution->date_opened)) {
            $institution->date_opened = new Time('01-01-1970');
            $Institution->save($institution);
        }

        $dateOpened = $institution->date_opened;
        try{
            $yearOpened = 1970;

            if (!empty($institution->year_opened)) {
                $yearOpened = $institution->year_opened;
            }
            $year = $dateOpened->format('Y');

            if ($yearOpened != $year) {
                $month = $dateOpened->format('m');
                $day = $dateOpened->format('d');
                $dateOpened = new Time($yearOpened.'-'.$month.'-'.$day);
                $institution->date_opened = $dateOpened;
                $Institution->save($institution);
            }
        } catch (\Exception $e) {
            $institution->date_opened = new Time('01-01-1970');
            $Institution->save($institution);
            $dateOpened = $institution->date_opened;
        }
        $this->fields['opening_date']['value'] = $dateOpened;
        $this->fields['opening_date']['date_options']['opening_date'] = $dateOpened->format('d-m-Y');
    }

    public function indexBeforeQuery(Event $event, Query $query, ArrayObject $extra){
        $selectedFacultiesId = $extra['selectedFacultiesId'];
        if($selectedFacultiesId > 0){
            if($selectedFacultiesId == 1 || $selectedFacultiesId == 2){
                $query
                    ->select([
                        $this->aliasField('id'),
                        $this->aliasField('name'),
                        $this->aliasField('short_name'),
                        $this->aliasField('opening_date'),
                        $this->aliasField('closing_date')
                    ])
                    ->where($this->getConditionWhereByFaculties($selectedFacultiesId));
            }
        }
    }

    public function indexBeforeAction(Event $event, ArrayObject $extra){
        $facultiesOptions = [1=>'Active', 2=>'Inactive'];
        $facultiesOptions = [-1 => __('All')] + $facultiesOptions;

        $Faculties = $this;
        $selectedFacultiesId = $this->queryString('faculties_id', $facultiesOptions);

        $this->advancedSelectOptions($facultiesOptions, $selectedFacultiesId, [
            'message' => '{{label}} - ' . __('noFaculties'),
            'callable' => function ($id) use ($Faculties) {

                /**
                 * If statement added on PHPOE-1762 for PHPOE-1766
                 * If $id is -1, get all classes under the selected academic period
                 */
                $query = $Faculties->find()
                    ->where($this->getConditionWhereByFaculties($id));
                return $query->count();
            }
        ]);


        $extra['selectedFacultiesId'] = $selectedFacultiesId;

        $extra['elements']['control'] = [
            'name' => 'Institution.Faculties/controls',
            'data' => [
                'facultiesOptions'=>$facultiesOptions,
                'selectedFaculties'=>$selectedFacultiesId,
            ],
            'options' => [],
            'order' => 3
        ];
    }

    function getConditionWhereByFaculties($id){
        if($id > 0){
            $currTime = Time::now();
            if($id == 1){
                return ['institution_id' => $this->institutionId, 'OR' => [
                    ['closing_date IS NULL'],
                    ["closing_date >= '" . $currTime->format('Y-m-d') . "'"]
                ]];
            }elseif ($id == 2){
                return ['institution_id' => $this->institutionId, 'AND' => [
                    ['closing_date IS NOT NULL'],
                    ["closing_date < '" . $currTime->format('Y-m-d') . "'"]
                ]];
            }
        }else{
            return ['institution_id' => $this->institutionId];
        }
    }
}
