<?php

namespace Institution\Model\Table;

use App\Model\Table\AppTable;
use Cake\Validation\Validator;

class MonMtbTable extends AppTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $this->belongsTo('Institutions', ['className' => 'Institution.Institutions']);
        $this->belongsTo('AcademicPeriods', ['className' => 'AcademicPeriod.AcademicPeriods']);
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
}
