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

class InstitutionExpenditureTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);

        $this->belongsTo('InstitutionReasonForExpenditure', ['className' => 'Institution.InstitutionReasonForExpenditure', 'foreignKey' => 'institution_reason_for_expenditure_id']);
        $this->belongsTo('InstitutionReasonForEntrance', ['className' => 'Institution.InstitutionReasonForEntrance', 'foreignKey' => 'institution_reason_for_entrance_id']);
    }
    public function beforeAction(Event $event, ArrayObject $extra)
    {
        $this->field('institution_reason_for_entrance_id', [ 'attr' => ['label' => __('Usage') ], 'after' => 'institution_reason_for_expenditure_id']);
    }
    public function indexBeforeAction(Event $event, ArrayObject $extra){
        //$this->field('institution_reason_for_entrance_id', ['visible'=> false]);
        //$this->field('article_of_receipt');
        $extra['elements']['controls'] = ['name' => 'Institution.Budget/controls', 'data' => [], 'options' => [], 'order' => 0];
    }

//    public function onGetArticleOfReceipt(Event $event, Entity $entity)
//    {
//        return __($this->InstitutionReasonForEntrance->get($entity->institution_reason_for_entrance_id)->name);
//    }

    public function indexBeforeQuery(Event $event, Query $query, ArrayObject $extra){

        $periodOptions = $this->AcademicPeriods->getYearList(['withLevels' => true, 'isEditable' => true]);

        $selectedPeriod = !is_null($this->request->query('academic_period_id')) ? $this->request->query('academic_period_id') : $this->AcademicPeriods->getCurrent();

        $this->controller->set(compact('periodOptions', 'selectedPeriod'));


        $where[$this->aliasField('academic_period_id')] = $selectedPeriod;

        $query->where($where);

    }

    public function addEditAfterAction(Event $event, Entity $entity){
        $this->field('academic_period_id', ['type' => 'select']);
        $this->field('institution_reason_for_expenditure_id', ['type' => 'select']);
        $this->field('institution_reason_for_entrance_id', ['type' => 'select' , 'attr' => ['label' => __('Usage') ]]);
        $this->field('amount_in_som', ['type' => 'float']);

    }

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra){

        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable'=>true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }

    private function maxAmoutnForSave($reasonEntranceId,$expenditureId, $academicId,$institutionId){

        $entrance = TableRegistry::get('institution_entrance');

        $sumAmountEntrance = 0;
        $sumAmountExpenditure = 0;

        if (!is_null($reasonEntranceId) && !is_null($expenditureId)){

            foreach ($this->find()->where([
                'id !='.$expenditureId,
                'institution_reason_for_entrance_id' => $reasonEntranceId,
                'academic_period_id' => $academicId,
                'institution_id' => $institutionId
            ])->all() as $item) {
                $sumAmountExpenditure += $item->amount_in_som;
            }

            foreach ($entrance->find()->where([
                'institution_reason_for_entrance_id' => $reasonEntranceId,
                'academic_period_id' => $academicId,
                'institution_id' => $institutionId
            ])->all() as $item) {
                $sumAmountEntrance += $item->amount_in_som;
            }
        }

        return $sumAmountEntrance - $sumAmountExpenditure;
    }

    public function validationDefault(Validator $validator){

        $validator = parent::validationDefault($validator);

        $validator->add('amount_in_som', 'minValue', [
            'rule' => function ($value, $context)  {
                $context=$context['data'];
                $id =($this->action == 'add') ? 0 : $context['id'];
                $maxValue = $this->maxAmoutnForSave($context['institution_reason_for_entrance_id'],$id,$context['academic_period_id'],$context['institution_id']);

                if ($value < 0 || $value > $maxValue) {

                    if ($maxValue == 0 ) {
                        return __('The value is not valid. This type of receipt has a zero balance');
                    }
                    else{
                        return __('The value is not valid. The value must not exceed ').' '.$maxValue;
                    }
                }

                return true;
            }
        ])->add('date', 'dateBetween', [
            'rule' => function ($value,$context)  {

                $academicPeriod = $this->AcademicPeriods->find()->where(['id' => $context['data']['academic_period_id']])->first()->start_date;

                $oldDate = strtotime(Time::parse($academicPeriod)->format('d-m-Y'));
                $value = strtotime(Time::parse($value)->format('d-m-Y'));
                $today = strtotime(Time::now()->format('d-m-Y'));

                if ($value >$today || $value < $oldDate ) {

                    return __("The date can't be from the future or far from the past");
                }

                return true;
            }
        ]);
        return $validator;
    }






}