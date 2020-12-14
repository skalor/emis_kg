<?php
namespace Institution\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use App\Model\Table\ControllerActionTable;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Validation\Validator;

class InstitutionExaminationGakTable extends ControllerActionTable
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

        $validator->add('academic_period_id', 'notDuplicated', [
            'rule' => function ($value,$context)  {
            $result = $this->find()->where(['academic_period_id' => $value, 'institution_id' => $context['data']['institution_id']])->first();
                if ($result) {
                    if ($result->id != $context['data']['id'])  return __('An entry already exists for this school year');
                }
                return true;
            }
        ]);
        return $validator;
    }
    public function indexBeforeAction(Event $event, ArrayObject $extra)
    {
        $extra['elements']['controls'] = ['name' => 'Institution.Gak/controls', 'data' => [], 'options' => [], 'order' => 0];
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