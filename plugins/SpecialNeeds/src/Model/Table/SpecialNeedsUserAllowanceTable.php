<?php
namespace SpecialNeeds\Model\Table;

use App\Date;
use ArrayObject;

use Cake\ORM\Entity;
use Cake\Network\Request;
use Cake\Event\Event;

use App\Model\Table\ControllerActionTable;
use App\Model\Traits\OptionsTrait;
use Cake\Validation\Validator;

class SpecialNeedsUserAllowanceTable extends ControllerActionTable
{
    use OptionsTrait;

    public function initialize(array $config)
    {
        parent::initialize($config);
        //$this->belongsTo('DisabilityGroups', ['className' => 'Health.DisabilityGroups', 'foreignKey' => 'health_disability_group_id']);
        $this->belongsTo('Users', ['className' => 'User.Users', 'foreignKey' => 'security_user_id']);
        $this->belongsTo('SpecialNeedsUserAllowanceTypes', ['className' => 'SpecialNeeds.SpecialNeedsUserAllowanceTypes', 'foreignKey' => 'special_needs_user_allowance_types_id']);

        $this->addBehavior('SpecialNeeds.SpecialNeeds');
    }

    public function addEditBeforeAction(Event $event, ArrayObject $extra)
    {
        //$this->field('health_disability_group_id', ['type' => 'select']);
    }

    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('special_needs_user_allowance_types_id', ['type' => 'select']);
    }

    public function onGetIsActiveAllowance(Event $event, Entity $entity)
    {
        $isActiveAllowanceOptions = $this->getSelectOptions('general.yesno');
        return $isActiveAllowanceOptions[$entity->is_active_allowance];
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        return  $validator
            ->add('start_date_allowance', 'notDuplicated', [
            'rule' => function ($value,$context)  {
                $context=$context['data'];
                $params[]  = "(('{$value}' BETWEEN `start_date_allowance` AND `end_date_allowance`) 
                OR ('{$context['end_date_allowance']}' BETWEEN `start_date_allowance` AND `end_date_allowance`) 
                OR (`start_date_allowance` BETWEEN '{$value}' AND '{$context['end_date_allowance']}') 
                OR (`end_date_allowance` BETWEEN '{$value}' AND '{$context['end_date_allowance']}'))";
                $params[]=['security_user_id'=>$context['security_user_id']];
                $counts=$this->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'count'
                ])
                    ->select([
                        'id',
                        'count' => $this->find()->func()->count('*')
                    ])
                    ->where($params)
                    ->group('id')
                    ->order(['id' => 'ASC'])
                    ->toArray();
                if (count($counts) <= 1) {
                    if (key($counts) == $context['id'] || empty($counts)) return true;
                }
                return __('A allowance record for this period already exists');
            }])
            ->add('end_date_allowance', 'endDateValidate', [
            'rule' => function ($value, $context) {
                if (strtotime($value) < strtotime($context['data']['start_date_allowance'])){
                    return __('The end date allowance is not valid. Must be higher than the start date allowance');
                }
                return true;
            }
        ]);
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }

    public function addEditAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }

    private function setupFields(Entity $entity)
    {
        $this->field('is_active_allowance');
    }

    public function onUpdateFieldIsActiveAllowance(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('general.yesno');
        return $attr;
    }
}
