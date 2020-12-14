<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\I18n\Time;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionEstimateTable extends ControllerActionTable
{
    public function initialize(array $config){
        parent::initialize($config);

        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('CalendarPeriod', ['className' => 'FieldOption.CalendarPeriod', 'foreignKey' => 'calendar_period_id']);

        $this->belongsTo('InstitutionReasonForEntrance', ['className' => 'Institution.InstitutionReasonForEntrance', 'foreignKey' => 'institution_reason_for_entrance_id']);
        $this->belongsTo('InstitutionReasonForExpenditure', ['className' => 'Institution.InstitutionReasonForExpenditure', 'foreignKey' => 'institution_reason_for_expenditure_id']);

    }

    public function indexBeforeAction(Event $event, ArrayObject $extra){

        $this->field('source',['after'=>'calendar_period_id']);
        $this->field('name_of_the_indicator',['after'=>'source']);
        $this->field('institution_reason_for_entrance_id', ['visible'=>false]);
        $this->field('institution_reason_for_expenditure_id', ['visible'=>false]);

    }

    public function onGetSource(Event $event, Entity $entity){

        $value = '';
        if ($entity->institution_reason_for_entrance_id){
            $value = $this->InstitutionReasonForEntrance->get($entity->institution_reason_for_entrance_id)->name;
        }
        return $value;
    }

    public function onGetNameOfTheIndicator(Event $event, Entity $entity){

        $value = '';
        if ($entity->institution_reason_for_expenditure_id){
            $value = $this->InstitutionReasonForExpenditure->get($entity->institution_reason_for_expenditure_id)->name;
        }
        return $value;
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        $validator->add('calendar_period_id', 'notDuplicated', [
            'rule' => function ($value,$context)  {
                $result = $this->find()
                    ->where([
                        'calendar_period_id' => $value,
                        'institution_reason_for_entrance_id' => $context['data']['institution_reason_for_entrance_id'],
                        'institution_id' => $context['data']['institution_id']])
                    ->first();
                if ($result) {
                    if ($result->id != $context['data']['id'])  return __('A record already exists for this period with this source type');
                }
                return true;
            }
        ]);
        return $validator;
    }

    public function addEditAfterAction(Event $event, Entity $entity){

        $this->field('calendar_period_id', ['type' => 'select']);
        $this->field('institution_reason_for_entrance_id', ['type' => 'select', 'attr'=>['label'=>__('Source')]]);
        $this->field('institution_reason_for_expenditure_id', ['type' => 'select', 'attr'=>['label'=>__('Name Of The Indicator')]]);
        $this->field('approved_budget', ['type' => 'float' ]);
        $this->field('revised_budget', ['type' => 'float' ]);
        $this->field('comment');


    }
}