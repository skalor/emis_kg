<?php
namespace Institution\Model\Table;
use ArrayObject;

use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionAttendanceTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
        $this->belongsTo('InstitutionPeriod', ['className' => 'Institution.InstitutionPeriod', 'foreignKey' => 'institution_period_id']);
    }

    public function beforeAction(){
        $this->fields['academic_period_id']['type'] = 'select';
        $this->fields['academic_period_id']['order'] = 1;

        $institutionId = $this->Session->read('Institution.Institutions.id');
        $periodOptions = $this->InstitutionPeriod->getOptionList($institutionId);
        $this->field('institution_period_id', [
            'type' => 'select',
            'order' => 2,
            'visible' => (is_null($periodOptions)) ? false : true ,
            'options' => $periodOptions
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        $validator->add('institution_period_id', 'notDuplicated', [
            'rule' => function ($value,$context)  {
                $result = $this
                    ->find()
                    ->where([
                        'institution_period_id' => $value,
                        'academic_period_id' => $context['data']['academic_period_id'],
                        'institution_id' => $context['data']['institution_id']])
                    ->first();
                if ($result) {
                    if ($result->id != $context['data']['id'])  return __('An entry already exists for this school period');
                }
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
