<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class InstitutionReasonForTransferTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->belongsTo('IdentityTypes', ['className' => 'FieldOption.IdentityTypes']);

        $this->hasMany('Institutions', ['className' => 'Institution.StudentAdmission', 'foreignKey' => 'institution_reason_for_transfer_id']);
        $this->hasMany('Institutions', ['className' => 'Institution.Students', 'foreignKey' => 'institution_reason_for_transfer_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'Students' => ['index', 'add']
        ]);
    }


}