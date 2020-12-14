<?php
namespace SpecialNeeds\Model\Table;

use App\Model\Table\ControllerActionTable;
use Cake\Validation\Validator;

class SpecialNeedsUserAllowanceTypesTable extends ControllerActionTable
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->hasMany('SpecialNeedsReferrals', ['className' => 'SpecialNeeds.SpecialNeedsUserAllowance', 'foreignKey' => 'special_needs_user_allowance_types_id', 'dependent' => true, 'cascadeCallbacks' => true]);

        $this->addBehavior('FieldOption.FieldOption');
    }


}