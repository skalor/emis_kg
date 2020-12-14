<?php
namespace Institution\Model\Table;
use ArrayObject;

use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionAggregatedDataSpoTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
    }

    public function beforeAction(){
        $this->fields['academic_period_id']['type'] = 'select';
        $this->fields['academic_period_id']['order'] = 1;
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        $validator->add('academic_period_id', 'notDuplicated', [
            'rule' => function ($value,$context)  {
                $result = $this->find()->where(['academic_period_id' => $value, 'institution_id' => $context['data']['institution_id']])->first();
                if ($result) {
                    if ($result->id != $context['data']['id'])  return __('An entry already exists for this school year');                }
                return true;
            }
        ]);
        return $validator;
    }
    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }
}
