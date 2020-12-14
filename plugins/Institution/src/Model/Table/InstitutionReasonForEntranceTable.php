<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class InstitutionReasonForEntranceTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);


        $this->hasMany('Entrance', ['className' => 'Institution.InstitutionEntrance', 'foreignKey' => 'institution_reason_for_entrance_id']);
        $this->hasMany('Expenditure', ['className' => 'Institution.InstitutionEntrance', 'foreignKey' => 'institution_reason_for_entrance_id']);
        $this->hasMany('Estimate', ['className' => 'Institution.InstitutionEstimate', 'foreignKey' => 'institution_reason_for_entrance_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'Institutions' => ['index', 'add']
        ]);
    }


}