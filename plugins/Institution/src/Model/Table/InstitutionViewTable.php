<?php
namespace Institution\Model\Table;

use ArrayObject;

use Cake\ORM\Query;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Network\Request;
use Cake\Validation\Validator;
use ControllerAction\Model\Traits\UtilityTrait;
use App\Model\Table\ControllerActionTable;
use App\Model\Traits\OptionsTrait;

class InstitutionViewTable extends ControllerActionTable
{
    use UtilityTrait;
    use OptionsTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->hasMany('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_view_id']);

        $this->belongsToMany('InstitutionTypes', [
            'className' => 'Institution.Types',
            'joinTable' => 'institution_type_view',
            'foreignKey' => 'institution_view_id',
            'targetForeignKey' => 'institution_types_id',
            'through' => 'Institution.InstitutionTypeView',
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);

        $this->addBehavior('FieldOption.FieldOption');
    }

    public function addEditBeforePatch(Event $event, Entity $entity, ArrayObject $requestData, ArrayObject $patchOptions, ArrayObject $extra)
    {
        if (array_key_exists($this->alias(), $requestData)) {
            if (isset($requestData[$this->alias()]['institution_types']['_ids']) && empty($requestData[$this->alias()]['institution_types']['_ids'])) {
                $requestData[$this->alias()]['institution_types'] = [];
            }
        }
    }


    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }


    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        return $validator
            ->allowEmpty('institution_types')
            ->add('institution_types', 'ruleCheckInstitutionsTypes', [
                'rule' => ['checkInstitutionsTypes'],
                'provider' => 'table',
            ]);
    }


    public function viewEditBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {
        $query->contain(['InstitutionTypes']);
    }

    public function onUpdateFieldInstitutionTypes(Event $event, array $attr, $action, Request $request)
    {
        $typeOptions = TableRegistry::get('Institution.Types')->getList()->toArray();
        $attr['options'] = $typeOptions;
        return $attr;
    }

    public function setupFields(Entity $entity)
    {
        $this->field('institution_types', [
            'type' => 'chosenSelect',
            'placeholder' => __('Select Types'),
            'visible' => ['index' => false, 'view' => true, 'edit' => true, 'add' => true],
            //'attr' => ['required' => true], // to add red asterisk
            'entity' => $entity,
            'after'=>'national_code'
        ]);
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }

    public function editAfterAction(Event $event, Entity $entity, ArrayObject $extra) {

        $this->setupFields($entity);
    }

    public function getOptionList($institutionTypeId){

        if (!is_null(intval($institutionTypeId))) {
            $classesId=TableRegistry::get('Institution.InstitutionTypeView')
                ->find('list')
                ->select(['institution_view_id'])
                ->where(['institution_types_id' => $institutionTypeId])
                ->toArray();
            if ($classesId){
                return $this
                    ->find('list')
                    ->where(['id IN '=>$classesId])
                    ->order([$this->aliasField('order')])
                    ->toArray();
            }
        }
        return null;

    }
}
