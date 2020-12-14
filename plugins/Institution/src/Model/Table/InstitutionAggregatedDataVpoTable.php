<?php

namespace Institution\Model\Table;

use ArrayObject;

use App\Model\Table\ControllerActionTable;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class InstitutionAggregatedDataVpoTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions', 'foreignKey' => 'institution_id']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
        $this->belongsTo('InstitutionPeriod', ['className' => 'Institution.InstitutionPeriod', 'foreignKey' => 'institution_period_id']);
        $this->addBehavior('OpenEmis.Section');
    }

    public function beforeAction()
    {
        $institutionTypes = TableRegistry::get('Institution.Types');
        $institutionId=$this->request->session()->read('Institution.Institutions.id');
        $this->fields['academic_period_id']['type'] = 'select';
        $this->fields['academic_period_id']['order'] = 1;

        $periodOptions = $this->InstitutionPeriod->getOptionList($institutionId);
        $this->field('institution_period_id', [
            'type' => 'select',
            'order' => 2,
            'visible' => (is_null($periodOptions)) ? false : true ,
            'options' => $periodOptions
        ]);

        $this->field('academic_performance', ['type' => 'section', 'title' => __('Academic performance') ,'after' => 'institution_period_id']);
        $this->field('ort_results', ['type' => 'section', 'title' => __('ORT results subjects average score') ,'after' => 'quality_academic_performance']);
        $this->field('olympiads', ['type' => 'section', 'title' => __('OLYMPIADS') ,'before' => 'history']);



            $institution = $this->Institutions->get($institutionId);
            $institutionTypeCode = $institutionTypes->get($institution->institution_type_id)->international_code;
            if ( $institutionTypeCode == "PRIMARY SECONDARY") {
                $this->fields['absolute_academic_performance']['visible'] = false;
                $this->fields['quality_academic_performance']['visible'] = false;
            }
            else if (in_array($institutionTypeCode,array('PRIMARY VOCATIONAL EDUCATIONAL ORGANIZATION', 'SECONDARY VOCATIONAL EDUCATIONAL ORGANIZATION','HIGHER PROFESSIONAL EDUCATIONAL ORGANIZATION')) ) {
                $this->fields['ort_results']['visible'] = false;
                $this->fields['olympiads']['visible'] = false;
                $this->fields['od_excellent']['visible'] = false;
                $this->fields['od_good_excellent']['visible'] = false;
                $this->fields['od_satisfactorily']['visible'] = false;
                $this->fields['od_mixed_grades']['visible'] = false;
                $this->fields['od_excellent_5_9']['visible'] = false;
                $this->fields['od_good_excellent_5_9']['visible'] = false;
                $this->fields['od_satisfactorily_5_9']['visible'] = false;
                $this->fields['od_mixed_grades_5_9']['visible'] = false;
                $this->fields['od_excellent_10_11']['visible'] = false;
                $this->fields['od_good_excellent_10_11']['visible'] = false;
                $this->fields['od_satisfactorily_10_11']['visible'] = false;
                $this->fields['od_mixed_grades_10_11']['visible'] = false;
                $this->fields['quality_academic_performance']['visible'] = false;
                $this->fields['main_test']['visible'] = false;
                $this->fields['maths']['visible'] = false;
                $this->fields['physics']['visible'] = false;
                $this->fields['english']['visible'] = false;
                $this->fields['chemistry']['visible'] = false;
                $this->fields['biology']['visible'] = false;
                $this->fields['history']['visible'] = false;
                $this->fields['number_of_participants_by_district']['visible'] = false;
                $this->fields['number_of_participants_by_region']['visible'] = false;
                $this->fields['republic_number_of_participants']['visible'] = false;
                $this->fields['number_of_participants_international']['visible'] = false;
                $this->fields['district_number_of_winners']['visible'] = false;
                $this->fields['region_number_of_winners']['visible'] = false;
                $this->fields['republic_number_of_winners']['visible'] = false;
                $this->fields['international_olympiads_number_of_winners']['visible'] = false;
                $this->fields['date']['visible'] = false;
                $this->fields['absolute_academic_performance']['visible'] = true;
                $this->fields['quality_academic_performance']['visible'] = true;
            }


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

    public function addAfterAction(Event $event, Entity $entity, ArrayObject $extra)
    {
        $academicPeriodOptions = $this->AcademicPeriods->getYearList(['isEditable' => true]);
        $this->fields['academic_period_id']['options'] = $academicPeriodOptions;
        $this->fields['academic_period_id']['default'] = $this->AcademicPeriods->getCurrent();
    }
}
