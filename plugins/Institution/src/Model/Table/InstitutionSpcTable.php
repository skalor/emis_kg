<?php
namespace Institution\Model\Table;
use ArrayObject;

use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionSpcTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
    }

    public function beforeAction(){
        $this->fields['academic_period_id']['type'] = 'select';
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);
        return $validator
            ->add('number_of_services_spc', 'lesserThan', [
                'rule' => function ($value)  {
                    if ($value < 0) {return __("Should not be lesser than 0"); }
                    return true;
                }
            ])->add('gross_revenue_spc', 'lesserThan', [
                'rule' => function ($value)  {
                    if ($value < 0) {return __("Should not be lesser than 0"); }
                    return true;
                }
            ])->add('gross_net_profit_spc', 'lesserThan', [
                'rule' => function ($value)  {
                    if ($value < 0) {return __("Should not be lesser than 0"); }
                    return true;
                }
            ])->add('number_students_who_purchased_patent', 'lesserThan', [
                'rule' => function ($value)  {
                    if ($value < 0) {return __("Should not be lesser than 0"); }
                    return true;
                }
            ])->add('number_students_who_organized_their_own_company', 'lesserThan', [
                'rule' => function ($value)  {
                    if ($value < 0) {return __("Should not be lesser than 0"); }
                    return true;
                }
            ]);

    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }
}