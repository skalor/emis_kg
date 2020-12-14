<?php
namespace Health\Model\Table;

use ArrayObject;

use Cake\ORM\Entity;
use Cake\Network\Request;
use Cake\Event\Event;

use App\Model\Table\ControllerActionTable;
use App\Model\Traits\OptionsTrait;
use Cake\Validation\Validator;

class PeopleDisabilitiesTable extends ControllerActionTable
{
    use OptionsTrait;

    public function initialize(array $config)
    {
        $this->table('user_people_disabilities');
        parent::initialize($config);
        $this->belongsTo('DisabilityGroups', ['className' => 'Health.DisabilityGroups', 'foreignKey' => 'health_disability_group_id']);
        $this->belongsTo('Users', ['className' => 'User.Users', 'foreignKey' => 'security_user_id']);

        $this->addBehavior('Health.Health');
    }

    public function addEditBeforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('health_disability_group_id', ['type' => 'select']);
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        $validator->add('from_disability', 'notDuplicated', [
            'rule' => function ($value,$context)  {
                $context=$context['data'];
                $params[]  = "(('{$value}' BETWEEN `from_disability` AND `to_disability`) OR ('{$context['to_disability']}' BETWEEN `from_disability` AND `to_disability`) OR (`from_disability` BETWEEN '{$value}' AND '{$context['to_disability']}') OR (`to_disability` BETWEEN '{$value}' AND '{$context['to_disability']}'))";
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
                return __('A disability record for this period already exists');
            }])
            ->add('to_disability', 'endDateValidate', [
            'rule' => function ($value, $context) {
                if (strtotime($value) < strtotime($context['data']['from_disability'])){
                    return __('The to disability is not valid. Must be higher than the from disability');
                }
                return true;
            }
        ]);
        return $validator;
    }

    public function viewAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }

    public function addEditAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $this->setupFields($entity);
    }

    public function onUpdateFieldExaminationType(Event $event, array $attr, $action, Request $request)
    {
        $attr['options'] = $this->getSelectOptions('Health.examination_type');
        return $attr;
    }

    private function setupFields(Entity $entity)
    {
        $this->field('examination_type');
    }
}
