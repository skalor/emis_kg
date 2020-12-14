<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionScholarshipsTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
    }

    public function addEditAfterAction(Event $event, Entity $entity)
    {
        $this->field('academic_period_id', ['type' => 'select']);
    }

    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        $validator->add('fellows_of_enterprises_institutions_and_organizations_human', 'differentValue', [
            'rule' => function ($value, $context)  {
                if ($value > $context['data']['number_of_students_receiving_the_scholarship_human']) {
                    return __('Scholarship holders of enterprises, institutions and organizations (person) should not exceed the Number of students receiving the scholarship (person)');
                }
                return true;
            }
        ]);
        return $validator;
    }
    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $this->setFieldOrder([
            'academic_period_id','the_accrued_scholarship_fund','number_of_students_receiving_the_scholarship_human','fellows_of_enterprises_institutions_and_organizations_human','average_established_scholarships_for_students'
        ]);
        $extra['elements']['controls'] = ['name' => 'Institution.Scholarships/controls', 'data' => [], 'options' => [], 'order' => 0];
    }

    public function indexBeforeQuery(Event $event, Query $query, ArrayObject $extra)
    {

        $periodOptions = $this->AcademicPeriods->getYearList(['withLevels' => true, 'isEditable' => true]);

        $selectedPeriod = !is_null($this->request->query('academic_period_id')) ? $this->request->query('academic_period_id') : $this->AcademicPeriods->getCurrent();

        $this->controller->set(compact('periodOptions', 'selectedPeriod'));


        $where[$this->aliasField('academic_period_id')] = $selectedPeriod;

        $query->where($where);

    }




}