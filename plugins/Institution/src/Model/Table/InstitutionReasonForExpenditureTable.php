<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class InstitutionReasonForExpenditureTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->hasMany('Expenditure', ['className' => 'Institution.InstitutionExpenditure', 'foreignKey' => 'institution_reason_for_expenditure_id']);
        $this->hasMany('Estimate', ['className' => 'Institution.InstitutionEstimate', 'foreignKey' => 'institution_reason_for_expenditure_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'Institutions' => ['index', 'add']
        ]);
    }


}