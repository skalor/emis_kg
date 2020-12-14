<?php
namespace Institution\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class ReasonForDismissalTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);


        $this->hasMany('Entrance', ['className' => 'Institution.StaffPositionProfiles', 'foreignKey' => 'reason_for_dismissal_id']);


        $this->addBehavior('FieldOption.FieldOption');


        $this->addBehavior('Restful.RestfulAccessControl', [
            'Institutions' => ['index', 'add']
        ]);
    }


}